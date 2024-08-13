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
    
            // Controleer of er spelers zijn in de divisie
            $players = Player::where('division_id', $division->id)->get();
            $standings = collect(); // Voeg deze regel toe om een lege collectie te initialiseren
    
            if ($players->isNotEmpty()) {
                $players = $players->load(['team.gamesHome' => function($query) use ($seasonId) {
                    $query->where('season_id', $seasonId)->whereNotNull('home_score')->whereNotNull('away_score');
                }, 'team.gamesAway' => function($query) use ($seasonId) {
                    $query->where('season_id', $seasonId)->whereNotNull('home_score')->whereNotNull('away_score');
                }]);
    
                $standings = $players->map(function ($player) {
                    $teamGames = $player->team->gamesHome->merge($player->team->gamesAway);
    
                    $gamesWon = $teamGames->reduce(function ($carry, $game) use ($player) {
                        return $carry + (($game->winner_id == $player->team_id) ? 1 : 0);
                    }, 0);
    
                    $gamesLost = $teamGames->reduce(function ($carry, $game) use ($player) {
                        return $carry + (($game->loser_id == $player->team_id) ? 1 : 0);
                    }, 0);
    
                    $gamesDrawn = $teamGames->reduce(function ($carry, $game) {
                        return $carry + (($game->home_score === $game->away_score) ? 1 : 0);
                    }, 0);
    
                    $matchesWon = 0;
                    $matchesLost = 0;
                    foreach ($teamGames as $game) {
                        foreach ($game->manches as $manche) {
                            if ($manche->winner_id == $player->id) {
                                $matchesWon++;
                            } else {
                                $matchesLost++;
                            }
                        }
                    }
    
                    // Puntensysteem: 1 punt per gewonnen manche, 0 punten voor verlies
                    $points = $matchesWon;
    
                    return [
                        'player_id' => $player->id,
                        'player_name' => $player->first_name . ' ' . $player->last_name,
                        'team_id' => $player->team->id,
                        'team_name' => $player->team->name,
                        'games_won' => $gamesWon,
                        'games_lost' => $gamesLost,
                        'games_drawn' => $gamesDrawn,
                        'matches_won' => $matchesWon,
                        'matches_lost' => $matchesLost,
                        'points' => $points,
                    ];
                });
    
                $standings = $standings->sortByDesc('points')->values();
            }
    
            return view('rankings.players', compact('division', 'players', 'seasonId', 'seasons', 'standings'));
        } catch (\Exception $e) {
            Log::error('Error in playerRankings method: ' . $e->getMessage());
            return back()->withErrors('Er is een fout opgetreden bij het ophalen van het spelersklassement.');
        }
    }
    

    
}
