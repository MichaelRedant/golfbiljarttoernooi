<?php

use App\Models\Season;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CupController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\DivisionController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/teams/by-club/{club}', [TeamController::class, 'getTeamsByClub']);
Route::get('/divisions/{division}/teams', [DivisionController::class, 'getTeamsByDivision']);

Route::get('/divisions/{division}/teams', function (Division $division) {
    return response()->json($division->teams);
});
Route::get('/api/division/{division_id}/seasons', function($division_id) {
    $seasons = Season::all();
    $currentSeasonId = Season::latest('id')->value('id');
    return response()->json([
        'seasons' => $seasons,
        'currentSeasonId' => $currentSeasonId
    ]);
});

Route::get('/divisions/{division_id}/seasons/{season_id}/teams', function($division_id, $season_id) {
    $teams = \App\Models\Team::whereHas('divisions', function ($query) use ($division_id) {
        $query->where('division_id', $division_id);
    })->whereHas('teamSeasonStats', function ($query) use ($season_id) {
        $query->where('season_id', $season_id);
    })->get();

    return response()->json($teams);
});


