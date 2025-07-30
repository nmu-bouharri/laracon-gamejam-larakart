<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Race;
use App\Models\RaceParticipant;
use App\Models\PhpDeveloper;
use App\Models\Car;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RaceController extends Controller
{
    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'track_name' => 'required|string|max:255',
            'max_players' => 'integer|min:2|max:8',
            'laps' => 'integer|min:1|max:10'
        ]);

        $race = Race::create([
            'name' => $request->name,
            'track_name' => $request->track_name,
            'status' => 'waiting',
            'max_players' => $request->max_players ?? 4,
            'current_players' => 0,
            'laps' => $request->laps ?? 3,
            'track_data' => $this->generateTrackData($request->track_name)
        ]);

        return response()->json([
            'success' => true,
            'race' => $race
        ]);
    }

    public function join(Request $request, Race $race): JsonResponse
    {
        $request->validate([
            'php_developer_id' => 'required|exists:php_developers,id',
            'car_id' => 'required|exists:cars,id'
        ]);

        if (!$race->canJoin()) {
            return response()->json([
                'success' => false,
                'message' => 'Race is full or already started'
            ], 400);
        }

        // Check if user is already in this race
        if ($race->participants()->where('user_id', Auth::id())->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'You are already in this race'
            ], 400);
        }

        $participant = RaceParticipant::create([
            'race_id' => $race->id,
            'user_id' => Auth::id(),
            'php_developer_id' => $request->php_developer_id,
            'car_id' => $request->car_id,
            'current_lap' => 0,
            'position_data' => ['x' => 0, 'y' => 0, 'z' => 0]
        ]);

        $race->increment('current_players');

        return response()->json([
            'success' => true,
            'participant' => $participant->load('phpDeveloper', 'car'),
            'race' => $race->fresh()
        ]);
    }

    public function start(Race $race): JsonResponse
    {
        if ($race->status !== 'waiting') {
            return response()->json([
                'success' => false,
                'message' => 'Race is not in waiting status'
            ], 400);
        }

        if ($race->current_players < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Need at least 2 players to start'
            ], 400);
        }

        $race->update([
            'status' => 'active',
            'started_at' => now()
        ]);

        // Broadcast race start event
        // broadcast(new RaceStarted($race));

        return response()->json([
            'success' => true,
            'race' => $race->fresh()
        ]);
    }

    public function show(Race $race): View
    {
        $race->load('participants.user', 'participants.phpDeveloper', 'participants.car');
        return view('game.race', compact('race'));
    }

    public function updatePosition(Request $request, Race $race): JsonResponse
    {
        $request->validate([
            'position_data' => 'required|array',
            'current_lap' => 'required|integer|min:0'
        ]);

        $participant = $race->participants()
            ->where('user_id', Auth::id())
            ->first();

        if (!$participant) {
            return response()->json([
                'success' => false,
                'message' => 'You are not in this race'
            ], 400);
        }

        $participant->update([
            'position_data' => $request->position_data,
            'current_lap' => $request->current_lap
        ]);

        // Broadcast position update
        // broadcast(new PositionUpdated($participant));

        return response()->json(['success' => true]);
    }

    private function generateTrackData(string $trackName): array
    {
        // Generate basic track data based on track name
        $tracks = [
            'Laravel Circuit' => [
                'length' => 2.5,
                'turns' => 12,
                'difficulty' => 'medium',
                'checkpoints' => [
                    ['x' => 100, 'y' => 0, 'z' => 200],
                    ['x' => 300, 'y' => 0, 'z' => 400],
                    ['x' => 500, 'y' => 0, 'z' => 200],
                    ['x' => 300, 'y' => 0, 'z' => 0]
                ]
            ],
            'PHP Speedway' => [
                'length' => 3.2,
                'turns' => 8,
                'difficulty' => 'easy',
                'checkpoints' => [
                    ['x' => 150, 'y' => 0, 'z' => 300],
                    ['x' => 450, 'y' => 0, 'z' => 300],
                    ['x' => 450, 'y' => 0, 'z' => 0],
                    ['x' => 150, 'y' => 0, 'z' => 0]
                ]
            ],
            'Symfony Grand Prix' => [
                'length' => 4.1,
                'turns' => 16,
                'difficulty' => 'hard',
                'checkpoints' => [
                    ['x' => 80, 'y' => 0, 'z' => 150],
                    ['x' => 250, 'y' => 0, 'z' => 350],
                    ['x' => 420, 'y' => 0, 'z' => 500],
                    ['x' => 600, 'y' => 0, 'z' => 250],
                    ['x' => 400, 'y' => 0, 'z' => 0]
                ]
            ]
        ];

        return $tracks[$trackName] ?? $tracks['Laravel Circuit'];
    }
}
