<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PhpDeveloper;
use App\Models\Car;
use App\Models\Race;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;

class GameController extends Controller
{
    public function index()
    {
        return Inertia::render('MainMenu');
    }

    public function selectCharacter(): View
    {
        $developers = PhpDeveloper::orderBy('popularity_rating', 'desc')
            ->orderBy('is_featured', 'desc')
            ->get();
            
        return view('game.select-character', compact('developers'));
    }

    public function selectCar(): View
    {
        $lambos = Car::lambos()->orderBy('speed_rating', 'desc')->get();
        $premiumCars = Car::premium()->where('is_lambo', false)->orderBy('overall_rating', 'desc')->get();
        $starterCars = Car::where('is_premium', false)->orderBy('unlock_level')->get();
        
        return view('game.select-car', compact('lambos', 'premiumCars', 'starterCars'));
    }

    public function lobby(): View
    {
        $waitingRaces = Race::waiting()->with('participants.user', 'participants.phpDeveloper', 'participants.car')->get();
        
        return view('game.lobby', compact('waitingRaces'));
    }

    public function getDevelopers(): JsonResponse
    {
        $developers = PhpDeveloper::all();
        return response()->json($developers);
    }

    public function getCars(): JsonResponse
    {
        $cars = Car::all();
        return response()->json($cars);
    }
}
