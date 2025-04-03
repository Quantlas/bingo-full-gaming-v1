<?php

use App\Http\Controllers\BingoCardsController;
use App\Http\Controllers\GamesController;
use App\Models\BingoCard;
use App\Models\Games;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Carbon\Carbon;

Route::get('/', function () {
    $scheduled_games = Games::where('status', 'scheduled')
        ->get()
        ->map(function ($game) {
            $game->date = Carbon::parse($game->start_time)->format('d-m-Y'); // Formato dd-mm-YYYY
            $game->time = Carbon::parse($game->start_time)->format('H:i:s'); // Separar la hora
            return $game;
        });

    $in_progress_games = Games::where('status', 'in_progress')
        ->get()
        ->map(function ($game) {
            $game->date = Carbon::parse($game->start_time)->format('d-m-Y');
            $game->time = Carbon::parse($game->start_time)->format('H:i:s');
            return $game;
        });

    return Inertia::render('Welcome', [
        'scheduled_games' => $scheduled_games,
        'in_progress_games' => $in_progress_games,
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::prefix('card')->group(function () {
    Route::get('/generate/{game_id}/{number?}', [BingoCardsController::class, 'generateBingoNumbers'])->name('bingo-card.generate');
    Route::post('/buy', [BingoCardsController::class, 'store'])->name('bingo-card.store');
});

Route::prefix('games')->group(function () {
    Route::get('/actives', [GamesController::class, 'getGames'])->name('games.getGames');
});


require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
