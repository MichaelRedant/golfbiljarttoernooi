<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Team;
use App\Models\User;
use App\Models\Season;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
{
    $user = Auth::user();
    $divisions = Division::all();
    $seasons = Season::all();
    $currentSeasonId = $request->query('season_id', Season::latest('id')->value('id'));
    $currentSeason = Season::findOrFail($currentSeasonId);

    if ($user->isAdmin()) {
        $pendingGames = Game::where('away_team_approved', false)->get();
        return view('dashboard.admin', compact('divisions', 'currentSeason', 'pendingGames'));
    } elseif ($user->isTeam()) {
        $team = $user->team;
        $teamRanking = $this->getTeamRanking($team->id, $currentSeason->id);
        $upcomingGames = Game::where('date', '>', now())
                            ->where(function($query) use ($team) {
                                $query->where('home_team_id', $team->id)
                                      ->orWhere('away_team_id', $team->id);
                            })->get();
        $pendingGames = Game::where('away_team_id', $team->id)
                            ->where('away_team_approved', false)
                            ->get();

        return view('dashboard.team', compact('team', 'currentSeason', 'seasons', 'teamRanking', 'upcomingGames', 'currentSeasonId', 'pendingGames'));
    } else {
        return view('dashboard.default');
    }
}

    public function teamDashboard()
    {
        $user = auth()->user();
        $teamId = $user->team_id;
        $currentSeasonId = request()->query('season_id', Season::latest('id')->value('id'));
    
        $seasons = Season::all();
        $currentSeason = Season::find($currentSeasonId);
    
        $teamRanking = $this->calculateTeamRanking($teamId, $currentSeasonId);
        $upcomingGames = Game::where(function ($query) use ($teamId) {
                                $query->where('home_team_id', $teamId)
                                      ->orWhere('away_team_id', $teamId);
                            })
                            ->where('date', '>=', now())
                            ->get();
                            
        $pendingGames = Game::where('away_team_id', $teamId)
                            ->where('away_team_approved', false)
                            ->get();
    
        return view('dashboard.team', compact('seasons', 'currentSeasonId', 'teamRanking', 'upcomingGames', 'pendingGames'));
    }

    private function getTeamRanking($teamId, $seasonId)
    {
        $team = Team::with(['gamesHome' => function ($query) use ($seasonId) {
            $query->where('season_id', $seasonId);
        }, 'gamesAway' => function ($query) use ($seasonId) {
            $query->where('season_id', $seasonId);
        }])->findOrFail($teamId);

        $games = $team->gamesHome->merge($team->gamesAway);
        
        $points = 0;
        $gamesWon = 0;
        $gamesLost = 0;
        $gamesDrawn = 0;

        foreach ($games as $game) {
            if ($game->home_team_id == $teamId) {
                if ($game->home_score > $game->away_score) {
                    $gamesWon++;
                    $points += 3;
                } elseif ($game->home_score < $game->away_score) {
                    $gamesLost++;
                } else {
                    $gamesDrawn++;
                    $points++;
                }
            } else {
                if ($game->away_score > $game->home_score) {
                    $gamesWon++;
                    $points += 3;
                } elseif ($game->away_score < $game->home_score) {
                    $gamesLost++;
                } else {
                    $gamesDrawn++;
                    $points++;
                }
            }
        }

        return [
            'team_name' => $team->name,
            'points' => $points,
            'games_won' => $gamesWon,
            'games_lost' => $gamesLost,
            'games_drawn' => $gamesDrawn
        ];
    }
}
