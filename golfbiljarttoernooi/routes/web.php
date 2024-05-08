<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

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

use App\Http\Controllers\GameController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\BelleController;
use App\Http\Controllers\MancheController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\SeasonController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\DivisionController;
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
Route::get('/get-teams', [PlayerController::class, 'getTeamsByDivision'])->name('get-teams');
Route::get('/get-players-by-team', [PlayerController::class, 'getPlayersByTeam'])->name('get-players-by-team');
Route::post('players/{player}/move-to-team', 'PlayerController@moveToTeam')->name('players.moveToTeam');

// Routes voor seasons
Route::resource('seasons', SeasonController::class);
Route::get('/seasons/create', [App\Http\Controllers\SeasonController::class, 'create'])->name('seasons.create');
Route::post('/seasons', [App\Http\Controllers\SeasonController::class, 'store'])->name('seasons.store');
Route::get('/seasons', [App\Http\Controllers\SeasonController::class, 'index'])->name('seasons.index');
Route::get('/seasons/{season}/edit', [App\Http\Controllers\SeasonController::class, 'edit'])->name('seasons.edit');
Route::put('/seasons/{season}', [App\Http\Controllers\SeasonController::class, 'update'])->name('seasons.update');
Route::delete('/seasons/{season}', [App\Http\Controllers\SeasonController::class, 'destroy'])->name('seasons.destroy');


// Routes voor Spelers
Route::get('/players', [PlayerController::class, 'index'])->name('players.index');
Route::get('/players/create', [PlayerController::class, 'create'])->name('players.create');
Route::post('/players', [PlayerController::class, 'store'])->name('players.store');
Route::get('/players/{player}', [PlayerController::class, 'show'])->name('players.show');
Route::get('/players/{player}/edit', [PlayerController::class, 'edit'])->name('players.edit');
Route::put('/players/{player}', [PlayerController::class, 'update'])->name('players.update');
Route::get('players/{player}/remove/{team}', 'PlayerController@removeFromTeam')->name('players.remove');
Route::delete('/players/{player}', [PlayerController::class, 'destroy'])->name('players.destroy');

// Routes voor Wedstrijden
Route::get('/games', [GameController::class, 'index'])->name('games.index');
Route::put('/games/{game}', [GameController::class, 'update'])->name('games.update');
Route::delete('/games/{game}', [GameController::class, 'destroy'])->name('games.destroy');
Route::get('/games/calendar-data', [GameController::class, 'calendarData'])->name('games.calendar-data');
Route::post('/games/generate', [GameController::class, 'generateMatches'])->name('games.generate');
Route::post('/games/clear', [GameController::class, 'clearCalendar'])->name('games.clear');
Route::get('/games/{game}/form', [GameController::class, 'editForm'])->name('games.form');
Route::get('/games/{game}', [GameController::class, 'show'])->name('games.show');

//Route voor Rankings
Route::get('/rankings', [RankingController::class, 'index'])->name('rankings.index');
Route::get('/rankings/{division}/teams', [RankingController::class, 'teamRankings'])->name('rankings.teams');
Route::get('/rankings/{division}/players', [RankingController::class, 'playerRankings'])->name('rankings.players');
Route::get('/team-standings', [TeamController::class, 'calculateTeamStandings'])->name('team.standings');
Route::get('/divisions/{divisionId}/standings', [PlayerController::class, 'calculatePlayerStandings'])->name('players.standings');
// Route om teams op te halen op basis van de divisie
Route::get('/get-teams', [TeamController::class, 'getTeamsByDivision'])->name('get-teams');
// Route om spelers op te halen op basis van het team
Route::get('/get-players-by-team', [PlayerController::class, 'getPlayersByTeam'])->name('get-players-by-team');





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


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
