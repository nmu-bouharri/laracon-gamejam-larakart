<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PhpDeveloper;
use App\Models\Car;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameController extends Controller
{
    /**
     * Get all available developers
     */
    public function getDevelopers(): JsonResponse
    {
        $developers = PhpDeveloper::orderBy('popularity_rating', 'desc')
            ->orderBy('is_featured', 'desc')
            ->get()
            ->map(function($developer) {
                return [
                    'id' => $developer->id,
                    'name' => $developer->name,
                    'bio' => $developer->bio,
                    'special_ability' => $developer->special_ability,
                    'popularity_rating' => $developer->popularity_rating,
                    'is_featured' => $developer->is_featured,
                    'is_locked' => $developer->is_locked,
                    'image_url' => $developer->avatar_url,
                ];
            });

        return response()->json($developers);
    }

    /**
     * Get all available cars
     */
    public function getCars(): JsonResponse
    {
        $cars = Car::orderBy('speed_rating', 'desc')
            ->get()
            ->map(function($car) {
                return [
                    'id' => $car->id,
                    'name' => $car->name,
                    'speed' => $car->speed_rating,
                    'acceleration' => $car->acceleration_rating,
                    'handling' => $car->handling_rating,
                    'drift' => $car->handling_rating, // Using handling for drift
                    'is_locked' => $car->unlock_level > 1, // Locked if unlock level > 1
                    'image_url' => $car->image_url,
                ];
            });

        return response()->json($cars);
    }

    /**
     * Unlock Taylor Otwell after beating him
     */
    public function unlockTaylor()
    {
        $taylor = PhpDeveloper::where('slug', 'taylor-otwell')->first();
        if ($taylor) {
            $taylor->update(['is_locked' => false]);
            return response()->json(['success' => true, 'message' => 'Taylor Otwell unlocked!']);
        }
        return response()->json(['success' => false, 'message' => 'Taylor not found']);
    }

    public function unlockNextDeveloper(Request $request)
    {
        $beatenDeveloper = $request->input('beaten_developer');
        
        // Find the next developer to unlock based on unlock_order
        $beaten = PhpDeveloper::where('slug', $beatenDeveloper)->first();
        if (!$beaten) {
            return response()->json(['success' => false, 'message' => 'Developer not found']);
        }

        $nextOrder = $beaten->unlock_order + 1;
        $nextDeveloper = PhpDeveloper::where('unlock_order', $nextOrder)->first();
        
        if ($nextDeveloper && $nextDeveloper->is_locked) {
            $nextDeveloper->update(['is_locked' => false]);
            return response()->json([
                'success' => true, 
                'message' => $nextDeveloper->name . ' unlocked!',
                'unlocked_developer' => $nextDeveloper->name
            ]);
        }
        
        return response()->json(['success' => true, 'message' => 'No more developers to unlock']);
    }
}
