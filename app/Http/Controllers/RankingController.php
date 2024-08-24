<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Division;
use App\Models\Player;
use App\Models\Season;
use App\Services\RankingService;
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

        // Bereken standings met behulp van RankingService
        $standings = $this->rankingService->calculateDivisionStandings($division, $seasonId);

        return view('rankings.teams', compact('division', 'standings', 'seasonId', 'seasons'));
    }

    public function playerRankings(Request $request, Division $division)
{
    try {
        $seasonId = $request->input('season_id', Season::latest()->first()->id);
        $seasons = Season::all();

        // Bereken de spelersklassementen via de RankingService
        $standings = $this->rankingService->calculatePlayerStandings($division->id, $seasonId);

        return view('rankings.players', compact('division', 'seasonId', 'seasons', 'standings'));
    } catch (\Exception $e) {
        Log::error('Error in playerRankings method: ' . $e->getMessage());
        return back()->withErrors('Er is een fout opgetreden bij het ophalen van het spelersklassement.');
    }
}

    
}
