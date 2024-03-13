<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\MancheController;
use App\Http\Controllers\BelleController;
use App\Http\Controllers\ReservePlayerController;

// Routes voor Divisies
Route::get('/divisions', [DivisionController::class, 'index'])->name('divisions.index');
Route::get('/divisions/create', [DivisionController::class, 'create'])->name('divisions.create');
Route::post('/divisions', [DivisionController::class, 'store'])->name('divisions.store');
Route::get('/divisions/{division}', [DivisionController::class, 'show'])->name('divisions.show');
Route::get('/divisions/{division}/edit', [DivisionController::class, 'edit'])->name('divisions.edit');
Route::put('/divisions/{division}', [DivisionController::class, 'update'])->name('divisions.update');
Route::get('/divisions/{division}/delete', [DivisionController::class, 'delete'])->name('divisions.delete');
Route::delete('/divisions/{division}', [DivisionController::class, 'destroy'])->name('divisions.destroy');
Route::get('/divisions/{divisionId}/teams', [PlayerController::class, 'getTeamsByDivision']);


// Routes voor Teams
Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
Route::get('/teams/create', [TeamController::class, 'create'])->name('teams.create');
Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
Route::get('/teams/{team}/edit', [TeamController::class, 'edit'])->name('teams.edit');
Route::put('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
Route::delete('/teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');
Route::get('/get-teams', [PlayerController::class, 'getTeams'])->name('get-teams');


// Routes voor Spelers
Route::get('/players', [PlayerController::class, 'index'])->name('players.index');
Route::get('/players/create', [PlayerController::class, 'create'])->name('players.create');
Route::post('/players', [PlayerController::class, 'store'])->name('players.store');
Route::get('/players/{player}', [PlayerController::class, 'show'])->name('players.show');
Route::get('/players/{player}/edit', [PlayerController::class, 'edit'])->name('players.edit');
Route::put('/players/{player}', [PlayerController::class, 'update'])->name('players.update');
Route::delete('/players/{player}', [PlayerController::class, 'destroy'])->name('players.destroy');

// Routes voor Wedstrijden
Route::get('/games', [GameController::class, 'index'])->name('games.index');
Route::put('/games/{game}', [GameController::class, 'update'])->name('games.update');
Route::delete('/games/{game}', [GameController::class, 'destroy'])->name('games.destroy');
Route::get('/games/calendar-data', [GameController::class, 'calendarData'])->name('games.calendar-data');
Route::post('/games/generate', [GameController::class, 'generateMatches'])->name('games.generate');
Route::post('/games/clear', [GameController::class, 'clearCalendar'])->name('games.clear');
Route::get('/games/{game}/form', [GameController::class, 'editForm'])->name('games.form');


//home
Route::get('/', function () {
    return view('home');
})->name('home');

// Routes voor Manches
Route::get('/manches', [MancheController::class, 'index'])->name('manches.index');
// Voeg hier routes toe voor andere acties voor manches

// Routes voor Belles
Route::get('/belles', [BelleController::class, 'index'])->name('belles.index');
// Voeg hier routes toe voor andere acties voor belles

// Routes voor Reservespelers
Route::get('/reserve-players', [ReservePlayerController::class, 'index'])->name('reserve_players.index');
// Voeg hier routes toe voor andere acties voor reservespelers

