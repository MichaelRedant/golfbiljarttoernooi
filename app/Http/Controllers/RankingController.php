<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Season;
use App\Services\RankingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RankingController extends Controller
{
    protected $rankingService;

    public function __construct(RankingService $rankingService)
    {
        $this->rankingService = $rankingService;
    }
 
    public function index(Request $request)
    {
        $seasons = Season::all();
        $latestSeason = Season::latest('id')->first();
        $currentSeasonId = $latestSeason ? $latestSeason->id : null;
        $divisions = Division::all();

        return view('rankings.index', compact('divisions', 'seasons', 'currentSeasonId'));
    }

    public function teamRankings(Request $request, Division $division)
{
    $seasonId = $request->input('season_id', Season::latest()->first()->id);
    $seasons = Season::all();

    $standings = $this->rankingService->calculateDivisionStandings($division, $seasonId);

    // Log de standings voor debuggen
    Log::info('Division Standings:', $standings);

    return view('rankings.teams', compact('division', 'standings', 'seasonId', 'seasons'));
}



public function playerRankings(Request $request, Division $division)
{
    try {
        $seasonId = $request->input('season_id', Season::latest()->first()->id);
        $seasons = Season::all();

        $standings = $this->rankingService->calculatePlayerStandings($division->id, $seasonId);

        // Log de standings voor debuggen
        Log::info('Player Standings:', $standings);

        return view('rankings.players', compact('division', 'seasonId', 'seasons', 'standings'));
    } catch (\Exception $e) {
        Log::error('Error in playerRankings method: ' . $e->getMessage());
        return back()->withErrors('Er is een fout opgetreden bij het ophalen van het spelersklassement.');
    }
}




}
