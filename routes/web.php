<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\RaceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Single page application entry point
Route::get('/', function () {
    return Inertia::render('App');
})->name('home');

// API Routes
Route::prefix('api')->group(function () {
    // Game data
    Route::get('/developers', [GameController::class, 'getDevelopers']);
    Route::get('/cars', [GameController::class, 'getCars']);
    Route::post('/unlock-taylor', [GameController::class, 'unlockTaylor']);
    Route::post('/unlock-next-developer', [GameController::class, 'unlockNextDeveloper']);
    
    // Lobby management
    Route::post('/lobby/join', [\App\Http\Controllers\Api\LobbyController::class, 'join']);
    Route::post('/lobby/add-ai', [\App\Http\Controllers\Api\LobbyController::class, 'addAI']);
    Route::post('/lobby/start-race', [\App\Http\Controllers\Api\LobbyController::class, 'startRace']);
    Route::get('/lobby/{lobbyKey}', [\App\Http\Controllers\Api\LobbyController::class, 'getLobby']);
    
    // Race management
    Route::prefix('races')->group(function () {
        Route::post('/', [RaceController::class, 'create']);
        Route::post('/{race}/join', [RaceController::class, 'join']);
        Route::post('/{race}/start', [RaceController::class, 'start']);
        Route::get('/{race}', [RaceController::class, 'show']);
        Route::post('/{race}/position', [RaceController::class, 'updatePosition']);
    });
});

// Catch-all route - must be last
Route::get('/{any}', function () {
    return Inertia::render('App');
})->where('any', '.*');
