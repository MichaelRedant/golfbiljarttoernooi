<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Team;
use App\Models\Player;
use App\Models\Season;
use App\Models\Division;
use Illuminate\Http\Request;
use App\Services\TeamStatsService;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $divisionId = $request->input('division');

        $teams = Team::query();

        if ($search) {
            $teams = $teams->where('name', 'LIKE', '%' . $search . '%');
        }

        if ($divisionId) {
            $teams = $teams->where('division_id', $divisionId);
            $division = Division::find($divisionId);
            $divisionName = $division ? $division->name : 'Geselecteerde divisie';
        } else {
            $divisionName = 'Alle divisies';
        }

        $teams = $teams->get();
        $divisions = Division::all();

        return view('teams.index', compact('teams', 'divisions', 'divisionName'));
    }
    public function create()
    {
        $divisions = Division::all();
        $clubs = Club::all();
        return view('teams.create', compact('divisions', 'clubs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'division_id' => 'required|exists:divisions,id',
            'location' => 'nullable|string|max:255'
        ]);

        Team::create($request->all());

        return redirect()->route('teams.index')->with('success', 'Team successfully created.');
    }

    public function show(Team $team, Request $request)
{
    $currentSeasonId = $request->query('season_id', Season::latest()->first()->id);
    $seasons = Season::all();

    $teamStats = $team->calculateStatsForSeason($currentSeasonId);

    $defaultStats = [
        'games_won' => 0,
        'games_lost' => 0,
        'games_draw' => 0,
        'points' => 0
    ];

    $teamStats = array_merge($defaultStats, $teamStats);

    $standings = $this->calculateDivisionStandings($team->division, $currentSeasonId);

    $currentTeamStanding = collect($standings)->firstWhere('team_id', $team->id);

    $players = $team->players; // Fetch players of the team

    return view('teams.show', compact('team', 'teamStats', 'seasons', 'currentSeasonId', 'standings', 'currentTeamStanding', 'players'));
}


    public function addresses()
    {
        $clubs = Club::with('teams')->get();
        return view('teams.addresses', compact('clubs'));
    }

    public function getTeamsByClub($clubId)
    {
        $teams = Team::where('club_id', $clubId)->get(['id', 'name', 'location']);
        return response()->json($teams);
    }

    protected function calculateRank(Team $team)
    {
        $teams = $team->division->teams()->with(['gamesHome', 'gamesAway'])->get();

        $rankings = $teams->map(function ($team) {
            $gamesWon = $team->gamesHome->where('home_score', '>', 'away_score')->count() +
                        $team->gamesAway->where('away_score', '>', 'home_score')->count();

            $gamesDraw = $team->gamesHome->where('home_score', '=', 'away_score')->count() +
                         $team->gamesAway->where('away_score', '=', 'home_score')->count();

            $points = $gamesWon * 3 + $gamesDraw;

            return [
                'team_id' => $team->id,
                'points' => $points,
                'games_won' => $gamesWon,
            ];
        })->sortByDesc(function ($team) {
            return [$team['points'], $team['games_won']];
        });

        $rank = $rankings->pluck('team_id')->search($team->id) + 1;

        return $rank;
    }

    public function edit(Team $team)
    {
        $divisions = Division::all();
        $players = $team->players;
        $allPlayers = Player::with('team')->get();

        return view('teams.edit', compact('team', 'divisions', 'players', 'allPlayers'));
    }

    public function update(Request $request, Team $team)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255'
        ]);

        $team->update($request->all());

        return redirect()->route('teams.index')->with('success', 'Team successfully updated.');
    }

    public function assignToTeam(Request $request, Team $team)
    {
        $player = Player::findOrFail($request->player_id);
        $player->team_id = $team->id;
        $player->save();

        return redirect()->route('teams.edit', $team)->with('success', 'Speler succesvol toegevoegd aan het team.');
    }

    public function removeFromTeam(Team $team, Player $player)
    {
        $player->team_id = null;
        $player->save();

        return redirect()->route('teams.edit', $team)->with('success', 'Speler succesvol verwijderd uit het team.');
    }

    public function calculateTeamStandings($divisionId, $currentSeasonId)
    {
        $teams = Team::where('division_id', $divisionId)
            ->with(['gamesHome' => function ($query) use ($currentSeasonId) {
                $query->where('season_id', $currentSeasonId)
                      ->whereNotNull('home_score')
                      ->whereNotNull('away_score');
            }, 'gamesAway' => function ($query) use ($currentSeasonId) {
                $query->where('season_id', $currentSeasonId)
                      ->whereNotNull('home_score')
                      ->whereNotNull('away_score');
            }])
            ->get();

        $standings = $teams->map(function ($team) {
            $gamesWon = $team->gamesHome->where('home_score', '>', 'away_score')->count() +
                        $team->gamesAway->where('away_score', '>', 'home_score')->count();

            $gamesLost = $team->gamesHome->where('home_score', '<', 'away_score')->count() +
                         $team->gamesAway->where('away_score', '<', 'home_score')->count();

            $gamesDraw = $team->gamesHome->where('home_score', '=', 'away_score')->count() +
                         $team->gamesAway->where('away_score', '=', 'home_score')->count();

            return [
                'team_id' => $team->id,
                'team_name' => $team->name,
                'games_won' => $gamesWon,
                'games_lost' => $gamesLost,
                'games_draw' => $gamesDraw,
                'points' => $gamesWon * 3 + $gamesDraw
            ];
        })->sortByDesc('points')->values()->all();

        return $standings;
    }
    

    private function calculateDivisionStandings(Division $division, $seasonId)
    {
        $teams = $division->teams()->with([
            'gamesHome' => function ($query) use ($seasonId) {
                $query->where('season_id', $seasonId)->whereNotNull('home_score')->whereNotNull('away_score');
            },
            'gamesAway' => function ($query) use ($seasonId) {
                $query->where('season_id', $seasonId)->whereNotNull('home_score')->whereNotNull('away_score');
            }
        ])->get();

        return $teams->map(function ($team) {
            $gamesWon = 0;
            $gamesLost = 0;
            $gamesDraw = 0;

            foreach ($team->gamesHome as $game) {
                if ($game->home_score > $game->away_score) {
                    $gamesWon++;
                } elseif ($game->home_score == $game->away_score) {
                    $gamesDraw++;
                } else {
                    $gamesLost++;
                }
            }

            foreach ($team->gamesAway as $game) {
                if ($game->away_score > $game->home_score) {
                    $gamesWon++;
                } elseif ($game->away_score == $game->home_score) {
                    $gamesDraw++;
                } else {
                    $gamesLost++;
                }
            }

            return [
                'team_id' => $team->id,
                'team_name' => $team->name,
                'games_won' => $gamesWon,
                'games_lost' => $gamesLost,
                'games_draw' => $gamesDraw,
                'points' => $gamesWon * 3 + $gamesDraw
            ];
        })->sortByDesc('points')->values()->all();
    }




    public function getTeamsByDivision(Request $request)
    {
        $divisionId = $request->query('division_id');
        $teams = Team::where('division_id', $divisionId)->get();
        return response()->json($teams);
    }

    public function moveToDivision(Request $request, Team $team)
    {
        $request->validate([
            'new_division_id' => 'required|exists:divisions,id',
        ]);

        $team->update(['division_id' => $request->new_division_id]);

        return back()->with('success', 'Team succesvol verplaatst naar een nieuwe divisie.');
    }

    public function removeFromDivision(Request $request, Team $team)
    {
        $team->division_id = null;
        $team->save();

        return redirect()->route('divisions.show', $team->division_id)->with('success', 'Team verwijderd uit divisie.');
    }

    public function destroy(Team $team)
    {
        $team->delete();

        return redirect()->route('teams.index')->with('success', 'Team deleted successfully.');
    }
}
