<?php

use App\Http\Controllers\CardsController;
use App\Http\Controllers\GamesController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('auth/Login');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Games
    Route::get('/games', [GamesController::class, 'index'])->name('games.index');
    Route::get('/games/all', [GamesController::class, 'getGames'])->name('games.getGames');
    Route::post('/games/create', [GamesController::class, 'create'])->name('games.create');
    Route::put('/games/{id}', [GamesController::class, 'update'])->name('games.update');
    Route::delete('/games/{id}', [GamesController::class, 'delete'])->name('games.delete');
    Route::post('/games/{id}/status', [GamesController::class, 'changeStatus'])->name('games.status');

    // Cards
    Route::get('/players', [CardsController::class, 'index'])->name('players.index');
    Route::put('/players/{id}', [CardsController::class, 'update'])->name('players.update');
    Route::get('/players/search', [CardsController::class, 'search'])->name('players.search');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
