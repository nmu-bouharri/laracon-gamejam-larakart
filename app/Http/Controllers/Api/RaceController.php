<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Race;
use App\Models\RaceParticipant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RaceController extends Controller
{
    /**
     * Create a new race
     */
    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'track_id' => 'required|exists:tracks,id',
            'max_players' => 'required|integer|min:2|max:4',
            'is_private' => 'boolean',
        ]);

        $race = Race::create([
            'name' => $validated['name'],
            'track_id' => $validated['track_id'],
            'max_players' => $validated['max_players'],
            'is_private' => $validated['is_private'] ?? false,
            'status' => 'waiting',
        ]);

        return response()->json($race, 201);
    }

    /**
     * Join an existing race
     */
    public function join(Request $request, Race $race): JsonResponse
    {
        // TODO: Add validation and authentication
        $participant = RaceParticipant::create([
            'race_id' => $race->id,
            'user_id' => 1, // Temp: Replace with auth()->id()
            'php_developer_id' => $request->input('php_developer_id'),
            'car_id' => $request->input('car_id'),
            'position' => 0,
            'is_ready' => false,
        ]);

        return response()->json($participant, 201);
    }

    /**
     * Start a race
     */
    public function start(Race $race): JsonResponse
    {
        // TODO: Add validation and authorization
        $race->update(['status' => 'in_progress']);
        return response()->json(['message' => 'Race started']);
    }

    /**
     * Get race details
     */
    public function show(Race $race): JsonResponse
    {
        return response()->json([
            'race' => $race->load(['participants', 'track']),
        ]);
    }

    /**
     * Update racer position
     */
    public function updatePosition(Request $request, Race $race): JsonResponse
    {
        // TODO: Add validation and authorization
        $participant = RaceParticipant::where('race_id', $race->id)
            ->where('user_id', 1) // Temp: Replace with auth()->id()
            ->firstOrFail();

        $participant->update([
            'position' => $request->input('position'),
            'lap' => $request->input('lap', 1),
            'checkpoint' => $request->input('checkpoint', 0),
        ]);

        // Broadcast position update to other players via WebSocket
        // TODO: Implement WebSocket broadcasting

        return response()->json(['success' => true]);
    }
}
