<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    ClubController, GameController, TeamController, UserController, BelleController, HomeController,
    MancheController, SponsorController, PlayerController, SeasonController, ProfileController, NewsController,
    RankingController, DivisionController, ReservePlayerController, DashboardController, Auth\AuthenticatedSessionController, Auth\PasswordResetLinkController, Auth\NewPasswordController
};

// Publicly accessible routes
Route::get('/clubs', [ClubController::class, 'index'])->name('clubs.index');
Route::get('/clubs/{club}', [ClubController::class, 'show'])->name('clubs.show');

Route::get('/divisions', [DivisionController::class, 'index'])->name('divisions.index');
Route::get('/divisions/{division}', [DivisionController::class, 'show'])->name('divisions.show');
Route::get('/divisions/{divisionId}/teams', [DivisionController::class, 'getTeamsByDivision']);

Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
Route::get('/team-addresses', [TeamController::class, 'addresses'])->name('teams.addresses');

Route::get('/players', [PlayerController::class, 'index'])->name('players.index');
Route::get('/players/{player}', [PlayerController::class, 'show'])->name('players.show');
Route::get('/get-teams', [PlayerController::class, 'getTeams'])->name('get-teams');
Route::get('/get-players-by-team', [PlayerController::class, 'getPlayersByTeam'])->name('get-players-by-team');
Route::get('/players/{player}/rankings', [PlayerController::class, 'getRankingsByDivision'])->name('players.rankings');

Route::get('/games/{game}', [GameController::class, 'show'])->name('games.show');
Route::get('/games/{game}', [GameController::class, 'showGame'])->name('games.show');
Route::get('/games/{game}/live-score', [GameController::class, 'fetchLiveScore'])->name('games.fetch-live-score');
Route::get('/games/fetchLiveScores', [GameController::class, 'fetchLiveScores'])->name('games.fetchLiveScores');
Route::get('/scores/stream', [GameController::class, 'streamScores'])->name('scores.stream');
Route::get('/games/{game}/fetch-latest', [GameController::class, 'fetchLatestGameData']);
Route::get('/kalender', [GameController::class, 'showCalendar'])->name('games.kalender');
Route::get('/innerlijk-reglement', [HomeController::class, 'reglement'])->name('reglement');
Route::get('/speelreglement', function () { return view('speelreglement');})->name('speelreglement');

Route::get('/rankings', [RankingController::class, 'index'])->name('rankings.index');
Route::get('/rankings/{division}/teams', [RankingController::class, 'teamRankings'])->name('rankings.teams');
Route::get('/rankings/{division}/players', [RankingController::class, 'playerRankings'])->name('rankings.players');

Route::get('/live-scores', [GameController::class, 'showLiveScores'])->name('live-scores');
Route::put('/games/{game}/update-live-score', [GameController::class, 'updateLiveScore'])->name('games.updateLiveScore');
Route::post('/games/{temporaryApproval}/confirm', [GameController::class, 'confirmApproval'])->name('games.confirmApproval');
Route::get('/update-all-player-stats', [GameController::class, 'updateAllPlayerStats']);
Route::match(['get', 'post'], 'games/{game}/approve', [GameController::class, 'approve'])->name('games.approve');

Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

// Home route
Route::get('/', [HomeController::class, 'index'])->name('home');

// News routes
Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/create', [NewsController::class, 'create'])->name('news.create');
Route::post('/news', [NewsController::class, 'store'])->name('news.store');
Route::get('/news/{news}/edit', [NewsController::class, 'edit'])->name('news.edit');
Route::patch('/news/{news}', [NewsController::class, 'update'])->name('news.update');
Route::delete('/news/{news}', [NewsController::class, 'destroy'])->name('news.destroy');
Route::patch('/news/{news}/toggle-sticky', [NewsController::class, 'toggleSticky'])->name('news.toggleSticky');

// Middleware-protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/game/create', [GameController::class, 'create'])->name('game.create');
    Route::post('/games', [GameController::class, 'store'])->name('games.store');
    Route::get('games/for-division-season/{division_id}/{season_id}', [GameController::class, 'showGamesForDivisionAndSeason'])->name('games.for-division-season');
    Route::get('games/for-team-season/{team_id}/{season_id}', [GameController::class, 'showGamesForTeamAndSeason'])->name('games.for-team-season');
    Route::get('/games/{game}/form', [GameController::class, 'editForm'])->name('games.form');
    Route::put('/games/{game}', [GameController::class, 'update'])->name('games.update');
    Route::delete('/games/{game}', [GameController::class, 'destroy'])->name('games.destroy');
    Route::get('/games/calendar-data', [GameController::class, 'calendarData'])->name('games.calendar-data');
    Route::post('/games/generate', [GameController::class, 'generateMatches'])->name('games.generate');
    Route::post('/games/clear', [GameController::class, 'clearCalendar'])->name('games.clear');
    Route::get('/games/{game}/play', [GameController::class, 'play'])->name('games.play');
    Route::get('/games/{game}/request-approval', [GameController::class, 'requestApproval'])->name('games.requestApproval');
    Route::post('/games/{game}/approve', [GameController::class, 'approve'])->name('games.approve');
    Route::post('/games/{game}/reject', [GameController::class, 'reject'])->name('games.reject');
    Route::post('/games/{game}/resubmit', [GameController::class, 'resubmit'])->name('games.resubmit');
    Route::get('/games/{game}/edit', [GameController::class, 'editForm'])->name('games.edit');
    Route::post('/games/{game}/forfeit', [GameController::class, 'forfeitRequest'])->name('games.forfeit')->middleware('ensureTeamIsAuthorized');
    Route::post('/games/{game}/confirm-forfeit', [GameController::class, 'confirmForfeit'])->name('games.confirm-forfeit');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/team', [DashboardController::class, 'teamDashboard'])->name('dashboard.team');

    Route::get('/profile/edit', [UserController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [UserController::class, 'update'])->name('profile.update');
});

// Admin routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::resource('divisions', DivisionController::class)->except(['index', 'show', 'create']);
    Route::post('divisions', [DivisionController::class, 'store'])->name('divisions.store');
    Route::get('division/create', [DivisionController::class, 'create'])->name('divisions.create');

    Route::resource('teams', TeamController::class)->except(['index', 'show', 'create']);
    Route::post('players/{player}/move-to-team', [TeamController::class, 'moveToTeam'])->name('players.moveToTeam');
    Route::post('/teams/{team}/remove', [TeamController::class, 'removeFromDivision'])->name('teams.remove');
    Route::post('/teams/{team}/move', [TeamController::class, 'moveToDivision'])->name('teams.move');
    Route::post('teams/{team}/assign', [TeamController::class, 'assignToTeam'])->name('players.assignToTeam');
    Route::delete('teams/{team}/remove/{player}', [TeamController::class, 'removeFromTeam'])->name('players.removeFromTeam');
    Route::post('teams', [TeamController::class, 'store'])->name('teams.store');
    Route::get('team/create', [TeamController::class, 'create'])->name('teams.create');

    Route::resource('seasons', SeasonController::class)->except(['show']);
    Route::resource('games', GameController::class)->except(['show']);
    Route::post('/games/bulk-approve', [GameController::class, 'bulkApprove'])->name('games.bulkApprove');

    Route::resource('players', PlayerController::class)->except(['index', 'show', 'create']);
    Route::get('players/{player}/remove/{team}', [PlayerController::class, 'removeFromTeam'])->name('players.remove');
    Route::post('players', [PlayerController::class, 'store'])->name('players.store');
    Route::get('/player/create', [PlayerController::class, 'create'])->name('players.create');

    Route::resource('clubs', ClubController::class)->except(['index', 'show', 'create']);
    Route::post('clubs', [ClubController::class, 'store'])->name('clubs.store');
    Route::get('/club/create', [ClubController::class, 'create'])->name('clubs.create');

    Route::resource('users', UserController::class)->except(['show']);
});

Route::resource('sponsors', SponsorController::class)->middleware('auth');

Route::get('/get-teams', [PlayerController::class, 'getTeamsByDivision'])->name('get-teams');
Route::get('/api/divisions/{division}/teams', [DivisionController::class, 'getTeamsByDivision']);
Route::get('/get-players-by-team', [PlayerController::class, 'getPlayersByTeam'])->name('get-players-by-team');

Route::get('/manches', [MancheController::class, 'index'])->name('manches.index');
Route::get('/belles', [BelleController::class, 'index'])->name('belles.index');
Route::get('/reserve-players', [ReservePlayerController::class, 'index'])->name('reserve_players.index');

require __DIR__ . '/auth.php';

