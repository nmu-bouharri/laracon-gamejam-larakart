<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Events\RaceCountdown;
use App\Events\RaceStarted;

class LobbyController extends Controller
{
    public function join(Request $request)
    {
        $playerId = $request->input('player_id', Str::uuid());
        $playerName = $request->input('player_name', 'Player');
        
        // Find available lobby or create new one
        $lobbyKey = $this->findOrCreateLobby();
        
        // Add player to lobby
        $lobby = Cache::get("lobby:{$lobbyKey}", [
            'key' => $lobbyKey,
            'players' => [],
            'status' => 'waiting',
            'created_at' => now()
        ]);
        
        // Check if player already in lobby
        $existingPlayerIndex = collect($lobby['players'])->search(function ($player) use ($playerId) {
            return $player['id'] === $playerId;
        });
        
        if ($existingPlayerIndex === false) {
            $lobby['players'][] = [
                'id' => $playerId,
                'name' => $playerName,
                'position' => count($lobby['players']) + 1,
                'ready' => false,
                'is_ai' => false
            ];
        }
        
        Cache::put("lobby:{$lobbyKey}", $lobby, 3600); // 1 hour TTL
        
        return response()->json([
            'lobby_key' => $lobbyKey,
            'player_id' => $playerId,
            'lobby' => $lobby
        ]);
    }
    
    public function addAI(Request $request)
    {
        $lobbyKey = $request->input('lobby_key');
        
        $lobby = Cache::get("lobby:{$lobbyKey}");
        if (!$lobby || count($lobby['players']) >= 4) {
            return response()->json(['error' => 'Lobby full or not found'], 400);
        }
        
        $aiNames = ['Taylor AI', 'Nuno AI', 'Caleb AI', 'Aaron AI'];
        $aiName = $aiNames[count($lobby['players'])];
        
        $lobby['players'][] = [
            'id' => 'ai_' . Str::uuid(),
            'name' => $aiName,
            'position' => count($lobby['players']) + 1,
            'ready' => true,
            'is_ai' => true
        ];
        
        Cache::put("lobby:{$lobbyKey}", $lobby, 3600);
        
        return response()->json(['lobby' => $lobby]);
    }
    
    public function startRace(Request $request)
    {
        $lobbyKey = $request->input('lobby_key');
        
        $lobby = Cache::get("lobby:{$lobbyKey}");
        if (!$lobby) {
            return response()->json(['error' => 'Lobby not found'], 404);
        }
        
        $lobby['status'] = 'countdown';
        $lobby['race_started_at'] = now();
        
        Cache::put("lobby:{$lobbyKey}", $lobby, 3600);
        
        // Start countdown sequence
        $this->startCountdownSequence($lobbyKey, $lobby['players']);
        
        return response()->json(['lobby' => $lobby]);
    }
    
    private function startCountdownSequence($lobbyKey, $players)
    {
        // Broadcast countdown: 3, 2, 1, GO!
        dispatch(function () use ($lobbyKey, $players) {
            sleep(1);
            broadcast(new RaceCountdown($lobbyKey, 3));
            
            sleep(1);
            broadcast(new RaceCountdown($lobbyKey, 2));
            
            sleep(1);
            broadcast(new RaceCountdown($lobbyKey, 1));
            
            sleep(1);
            broadcast(new RaceCountdown($lobbyKey, 'GO!'));
            
            // Start the actual race
            broadcast(new RaceStarted($lobbyKey, $players));
            
            // Update lobby status
            $lobby = Cache::get("lobby:{$lobbyKey}");
            $lobby['status'] = 'racing';
            Cache::put("lobby:{$lobbyKey}", $lobby, 3600);
        });
    }
    
    public function getLobby(Request $request, $lobbyKey)
    {
        $lobby = Cache::get("lobby:{$lobbyKey}");
        
        if (!$lobby) {
            return response()->json(['error' => 'Lobby not found'], 404);
        }
        
        return response()->json(['lobby' => $lobby]);
    }
    
    private function findOrCreateLobby()
    {
        // Get all lobby keys from cache
        $allKeys = Cache::get('lobby_keys', []);
        
        // Find available lobby (less than 4 players)
        foreach ($allKeys as $key) {
            $lobby = Cache::get("lobby:{$key}");
            if ($lobby && count($lobby['players']) < 4 && $lobby['status'] === 'waiting') {
                return $key;
            }
        }
        
        // Create new lobby
        $newKey = Str::random(8);
        $allKeys[] = $newKey;
        Cache::put('lobby_keys', $allKeys, 3600);
        
        return $newKey;
    }
}
