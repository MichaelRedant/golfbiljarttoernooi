<?php

namespace App\Http\Controllers;
use App\Models\Game;
use App\Models\Team;
use App\Models\User;
use App\Models\Season;
use App\Models\Division;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
{
    $user = auth()->user();
    $divisions = Division::all();
    $currentSeason = Season::latest('id')->first();

    if ($user->isAdmin()) {
        return view('dashboard.admin', compact('divisions', 'currentSeason'));
    } elseif ($user->isTeam()) {
        $team = $user->team;
        return view('dashboard.team', compact('team', 'currentSeason'));
    } else {
        return view('dashboard.default');
    }
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
