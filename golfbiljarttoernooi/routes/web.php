<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BelleController;
use App\Http\Controllers\MancheController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\SeasonController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\ReservePlayerController;

Route::group(['middleware' => ['auth', 'admin']], function () {
    // Divisions routes
    Route::get('/divisions/create', [DivisionController::class, 'create'])->name('divisions.create');
    Route::post('/divisions', [DivisionController::class, 'store'])->name('divisions.store');
    Route::get('/divisions/{division}/edit', [DivisionController::class, 'edit'])->name('divisions.edit');
    Route::put('/divisions/{division}', [DivisionController::class, 'update'])->name('divisions.update');
    Route::get('/divisions/{division}/delete', [DivisionController::class, 'delete'])->name('divisions.delete');
    Route::delete('/divisions/{division}', [DivisionController::class, 'destroy'])->name('divisions.destroy');

    // Teams routes
    Route::get('/teams/create', [TeamController::class, 'create'])->name('teams.create');
    Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
    Route::get('/teams/{team}/edit', [TeamController::class, 'edit'])->name('teams.edit');
    Route::put('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
    Route::delete('/teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');
    Route::post('players/{player}/move-to-team', [TeamController::class, 'moveToTeam'])->name('players.moveToTeam');
    Route::post('/teams/{team}/remove', 'TeamController@removeFromDivision')->name('teams.remove');
    Route::post('/teams/{team}/move', 'TeamController@moveToDivision')->name('teams.move');

    // Seasons routes
    Route::get('/seasons/create', [SeasonController::class, 'create'])->name('seasons.create');
    Route::post('/seasons', [SeasonController::class, 'store'])->name('seasons.store');
    Route::get('/seasons', [SeasonController::class, 'index'])->name('seasons.index');
    Route::get('/seasons/{season}/edit', [SeasonController::class, 'edit'])->name('seasons.edit');
    Route::put('/seasons/{season}', [SeasonController::class, 'update'])->name('seasons.update');
    Route::delete('/seasons/{season}', [SeasonController::class, 'destroy'])->name('seasons.destroy');

    // Players routes
    Route::get('/players/create', [PlayerController::class, 'create'])->name('players.create');
    Route::post('/players', [PlayerController::class, 'store'])->name('players.store');
    Route::get('/players/{player}/edit', [PlayerController::class, 'edit'])->name('players.edit');
    Route::put('/players/{player}', [PlayerController::class, 'update'])->name('players.update');
    Route::get('players/{player}/remove/{team}', [PlayerController::class, 'removeFromTeam'])->name('players.remove');
    Route::delete('/players/{player}', [PlayerController::class, 'destroy'])->name('players.destroy');
    Route::get('/search-players', [PlayerController::class, 'searchPlayers']);
});

Route::middleware(['auth', 'admin'])->group(function () {
    // Clubs routes
    Route::get('/clubs', [ClubController::class, 'index'])->name('clubs.index');
    Route::get('/clubs/create', [ClubController::class, 'create'])->name('clubs.create');
    Route::post('/clubs', [ClubController::class, 'store'])->name('clubs.store');
    Route::get('/clubs/{club}', [ClubController::class, 'show'])->name('clubs.show');
    Route::get('/clubs/{club}/edit', [ClubController::class, 'edit'])->name('clubs.edit');
    Route::put('/clubs/{club}', [ClubController::class, 'update'])->name('clubs.update');
    Route::delete('/clubs/{club}', [ClubController::class, 'destroy'])->name('clubs.destroy');
});

// Publicly accessible clubs routes
Route::get('/clubs', [ClubController::class, 'index'])->name('clubs.index');
Route::get('/clubs/{club}', [ClubController::class, 'show'])->name('clubs.show');

// Divisions routes
Route::get('/divisions', [DivisionController::class, 'index'])->name('divisions.index');
Route::get('/divisions/{division}', [DivisionController::class, 'show'])->name('divisions.show');
Route::get('/divisions/{divisionId}/teams', [PlayerController::class, 'getTeamsByDivision']);

// Teams routes
Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
Route::get('/get-teams', [PlayerController::class, 'getTeamsByDivision'])->name('get-teams');
Route::get('/get-players-by-team', [PlayerController::class, 'getPlayersByTeam'])->name('get-players-by-team');
Route::get('/team-addresses', [TeamController::class, 'addresses'])->name('teams.addresses');

// Seasons routes
Route::resource('seasons', SeasonController::class);

// Players routes
Route::get('/players', [PlayerController::class, 'index'])->name('players.index');
Route::get('/players/{player}', [PlayerController::class, 'show'])->name('players.show');

// Games routes
Route::get('/games', [GameController::class, 'index'])->name('games.index');
Route::get('/games/{game}/form', [GameController::class, 'editForm'])->name('games.form');
Route::get('/games/{game}', [GameController::class, 'show'])->name('games.show');
Route::post('/games', [GameController::class, 'store'])->name('games.store');
Route::put('/games/{game}', [GameController::class, 'update'])->name('games.update');
Route::delete('/games/{game}', [GameController::class, 'destroy'])->name('games.destroy');
Route::get('/games/calendar-data', [GameController::class, 'calendarData'])->name('games.calendar-data');
Route::post('/games/generate', [GameController::class, 'generateMatches'])->name('games.generate');
Route::post('/games/clear', [GameController::class, 'clearCalendar'])->name('games.clear');
Route::get('/games/{game}/play', [GameController::class, 'play'])->name('games.play');
Route::get('/games/{game}/edit', [GameController::class, 'edit'])->name('games.edit');
Route::get('/games/create/{division_id?}/{season_id?}', [GameController::class, 'create'])->name('games.create');
Route::get('/games/{division_id}/{season_id}', [GameController::class, 'showGamesForDivisionAndSeason'])->name('games.for-division-season');

// Rankings routes
Route::get('/rankings', [RankingController::class, 'index'])->name('rankings.index');
Route::get('/rankings/{division}/teams', [RankingController::class, 'teamRankings'])->name('rankings.teams');
Route::get('/rankings/{division}/players', [RankingController::class, 'playerRankings'])->name('rankings.players');
Route::get('/team-standings', [TeamController::class, 'calculateTeamStandings'])->name('teams.standings');
Route::get('/divisions/{divisionId}/standings', [PlayerController::class, 'calculatePlayerStandings'])->name('players.standings');

// Additional team and player routes
Route::get('/get-teams', [TeamController::class, 'getTeamsByDivision'])->name('get-teams');
Route::get('/get-players-by-team', [PlayerController::class, 'getPlayersByTeam'])->name('get-players-by-team');

// Home route
Route::get('/', function () {
    return view('home');
})->name('home');

// Manches routes
Route::get('/manches', [MancheController::class, 'index'])->name('manches.index');

// Belles routes
Route::get('/belles', [BelleController::class, 'index'])->name('belles.index');

// Reserve Players routes
Route::get('/reserve-players', [ReservePlayerController::class, 'index'])->name('reserve_players.index');

// Dashboard route
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/dashboard', [GameController::class, 'showDashboard'])->middleware(['auth', 'verified'])->name('dashboard');

// User routes
Route::middleware('auth')->group(function () {
    Route::delete('/users/delete', [UserController::class, 'destroy'])->name('users.destroy');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::get('/profile/edit', [UserController::class, 'edit'])->name('profile.edit');
});

// Include authentication routes
require __DIR__.'/auth.php';