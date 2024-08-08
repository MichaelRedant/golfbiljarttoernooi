<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Team;
use App\Models\User;
use App\Models\Season;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        Log::info('DashboardController@index reached');

        $user = Auth::user();
        if (!$user) {
            Log::error('No authenticated user found.');
            return redirect('/login');
        }

        Log::info('Authenticated user', ['user' => $user]);

        $divisions = Division::all();
        Log::info('Divisions retrieved', ['divisions_count' => $divisions->count()]);

        $seasons = Season::all();
        Log::info('Seasons retrieved', ['seasons_count' => $seasons->count()]);

        $currentSeasonId = $request->query('season_id', Season::latest('id')->value('id'));
        $currentSeason = $currentSeasonId ? Season::find($currentSeasonId) : null;
        Log::info('Current season', ['currentSeason' => $currentSeason]);

        if ($user->role === 'admin') {
            $pendingGames = Game::where('away_team_approved', false)->get();
            Log::info('Pending games for admin', ['pending_games_count' => $pendingGames->count()]);
            return view('dashboard.admin', compact('divisions', 'currentSeason', 'pendingGames', 'seasons'));
        } elseif ($user->role === 'team') {
            $team = $user->team;
            if (!$team) {
                Log::warning('No team associated with the user');
                return view('dashboard.team', [
                    'message' => 'Geen team gekoppeld aan de gebruiker.',
                    'seasons' => $seasons,
                    'currentSeason' => $currentSeason,
                    'currentSeasonId' => $currentSeasonId,
                    'teamRanking' => null,
                    'upcomingGames' => collect(),
                    'pendingGames' => collect(),
                ]);
            }

            $teamRanking = $this->getTeamRanking($team->id, $currentSeason ? $currentSeason->id : null);
            $upcomingGames = Game::where('date', '>', now())
                                ->where(function($query) use ($team) {
                                    $query->where('home_team_id', $team->id)
                                          ->orWhere('away_team_id', $team->id);
                                })->get();
            $pendingGames = Game::where('away_team_id', $team->id)
                                ->where('away_team_approved', false)
                                ->get();

            Log::info('Team dashboard data', [
                'team' => $team,
                'teamRanking' => $teamRanking,
                'upcomingGames' => $upcomingGames,
                'pendingGames' => $pendingGames
            ]);

            return view('dashboard.team', compact('team', 'currentSeason', 'seasons', 'teamRanking', 'upcomingGames', 'currentSeasonId', 'pendingGames'));
        } else {
            Log::info('Default dashboard');
            return view('dashboard.default');
        }
    }


    private function getTeamRanking($teamId, $seasonId)
    {
        Log::info('Getting team ranking', ['teamId' => $teamId, 'seasonId' => $seasonId]);
        
        if (is_null($seasonId)) {
            return null;
        }

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

        $teamRanking = [
            'team_name' => $team->name,
            'points' => $points,
            'games_won' => $gamesWon,
            'games_lost' => $gamesLost,
            'games_drawn' => $gamesDrawn
        ];

        Log::info('Team ranking calculated', ['teamRanking' => $teamRanking]);

        return $teamRanking;
    }

    public function teamDashboard()
    {
        $user = Auth::user();
        $teamId = $user->team_id;
        $currentSeasonId = request()->query('season_id', Season::latest('id')->value('id'));

        $seasons = Season::all();
        $currentSeason = Season::find($currentSeasonId);

        $teamRanking = $this->getTeamRanking($teamId, $currentSeasonId);
        $upcomingGames = Game::where(function ($query) use ($teamId) {
                                $query->where('home_team_id', $teamId)
                                      ->orWhere('away_team_id', $teamId);
                            })
                            ->where('date', '>=', now())
                            ->get();

        $pendingGames = Game::where('away_team_id', $teamId)
                            ->where('away_team_approved', false)
                            ->get();

        return view('dashboard.team', compact('seasons', 'currentSeasonId', 'teamRanking', 'upcomingGames', 'pendingGames', 'currentSeason'));
    }
}
