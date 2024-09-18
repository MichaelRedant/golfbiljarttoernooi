<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Division;
use App\Models\Player;
use App\Models\PlayerSeasonStat;
use App\Models\Season;
use App\Models\Team;
use App\Services\RankingService;
use App\Services\TeamStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TeamController extends Controller
{
    protected $rankingService;

    public function __construct(RankingService $rankingService)
    {
        $this->rankingService = $rankingService;
    }

    public function index(Request $request)
{
    $search = $request->input('search');
    $divisionId = $request->input('division');
    $sortField = $request->input('sort_field', 'name');
    $sortOrder = $request->input('sort_order', 'asc');

    $teams = Team::with('club');

    if ($search) {
        $teams = $teams->where('name', 'LIKE', '%' . $search . '%');
    }

    if ($divisionId) {
        $teams = $teams->whereHas('divisions', function ($query) use ($divisionId) {
            $query->where('division_id', $divisionId);
        });
        $division = Division::find($divisionId);
        $divisionName = $division ? $division->name : 'Geselecteerde reeks';
    } else {
        $divisionName = 'Alle reeksen';
    }

    if ($sortField == 'club_name') {
        $teams = $teams->leftJoin('clubs', 'teams.club_id', '=', 'clubs.id')
                       ->orderBy('clubs.name', $sortOrder)
                       ->select('teams.*');
    } else {
        $teams = $teams->orderBy($sortField, $sortOrder);
    }

    $teams = $teams->get();
    $divisions = Division::all();

    return view('teams.index', compact('teams', 'divisions', 'divisionName', 'sortField', 'sortOrder'));
}


    
    public function create(Request $request)
    {
        $clubId = $request->query('club_id');
        $divisions = Division::all();
        $clubs = Club::all();
        $players = Player::all();
        return view('teams.create', compact('divisions', 'clubs', 'players', 'clubId'));
    }

    public function store(Request $request)
{
    try {
        Log::info('Store method called');
        Log::info('Request data: ', $request->all());

        $request->validate([
            'name' => 'required|string|max:255',
            'club_id' => 'required|exists:clubs,id',
            'division_ids' => 'array',
            'division_ids.*' => 'exists:divisions,id',
            'location' => 'nullable|string|max:255',
        ]);

        // Create team without division_id
        $team = Team::create($request->only('name', 'club_id', 'location'));

        // Synchronize the divisions, allowing for no selection
        if ($request->has('division_ids')) {
            $team->divisions()->sync($request->division_ids);
        }

        return redirect()->route('teams.edit', $team)->with('success', 'Team succesvol toegevoegd.');
    } catch (\Exception $e) {
        Log::error('Error storing team: ' . $e->getMessage());
        return back()->with('error', 'Er is een fout opgetreden bij het toevoegen van het team. Controleer de invoer en probeer het opnieuw.');
    }
}


public function show(Team $team, Request $request)
{
    Log::info('TeamController@show reached', ['team' => $team]);

    $error = null;

    // Haal het laatste seizoen op
    $latestSeason = Season::latest()->first();
    if (!$latestSeason) {
        Log::error('No active season found');
        $error = 'Geen actief seizoen gevonden. Zorg ervoor dat er minstens één seizoen is toegevoegd.';
        return view('teams.show', [
            'team' => $team,
            'teamStats' => [],
            'seasons' => collect(),
            'divisions' => collect(),
            'currentSeasonId' => null,
            'currentDivisionId' => null,
            'standings' => collect(),
            'currentTeamStanding' => null,
            'teamPlayerStats' => collect(), // Maak een lege collectie aan
            'error' => $error
        ]);
    }

    // Gebruik het laatste seizoen en de bijbehorende divisie als standaard
    $currentSeasonId = $request->query('season_id', $latestSeason->id);
    $currentDivisionId = $request->query('division_id', $team->divisions->first()->id ?? null);
    Log::info('Current season and division selected', ['currentSeasonId' => $currentSeasonId, 'currentDivisionId' => $currentDivisionId]);

    $seasons = Season::all();
    $divisions = $team->divisions;
    Log::info('Seasons and divisions loaded', ['seasons' => $seasons, 'divisions' => $divisions]);

    // Bereken de teamstatistieken voor het huidige seizoen en divisie
    $defaultStats = [
        'games_won' => 0,
        'games_lost' => 0,
        'games_draw' => 0,
        'points' => 0
    ];

    $teamStats = $team->calculateStatsForSeasonAndDivision($currentSeasonId, $currentDivisionId);
    $teamStats = array_merge($defaultStats, $teamStats ?? []);
    Log::info('Team stats calculated', ['teamStats' => $teamStats]);

    // Bereken de standen van de divisie via de RankingService
    $division = Division::find($currentDivisionId);
    if (!$division) {
        $standings = [];
        $currentTeamStanding = null;
    } else {
        $standings = app(RankingService::class)->calculateDivisionStandings($division, $currentSeasonId);
        $currentTeamStanding = collect($standings)->firstWhere('team_id', $team->id);
    }

    Log::info('Division standings calculated', ['standings' => $standings, 'currentTeamStanding' => $currentTeamStanding]);

    // Haal de spelerstatistieken op via de RankingService
    $playerStats = app(RankingService::class)->calculatePlayerStandingsForAllDivisions($team, $currentSeasonId);
    
    // Filter de spelerstatistieken zodat alleen de spelers van het team getoond worden
    $teamPlayerStats = collect($playerStats)->filter(function($playerStat) use ($team) {
        return $playerStat['team_id'] === $team->id;
    })->values();

    Log::info('Player stats for the team', ['teamPlayerStats' => $teamPlayerStats]);

    // Return de view met de correcte variabelen
    return view('teams.show', compact(
        'team',
        'teamStats', 
        'seasons', 
        'divisions', 
        'currentSeasonId', 
        'currentDivisionId', 
        'standings', 
        'currentTeamStanding', 
        'teamPlayerStats', 
        'error'
    ));
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
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'location' => 'nullable|string|max:255',
                'division_ids' => 'array',
                'division_ids.*' => 'exists:divisions,id',
            ]);

            $team->update($request->only('name', 'location'));

            if ($request->has('division_ids')) {
                $team->divisions()->sync($request->division_ids);
            } else {
                $team->divisions()->sync([]);
            }

            return redirect()->route('teams.index')->with('success', 'Team succesvol bijgewerkt.');
        } catch (\Exception $e) {
            Log::error('Error updating team: ' . $e->getMessage());
            return back()->with('error', 'Er is een fout opgetreden bij het bijwerken van het team. Controleer de invoer en probeer het opnieuw.');
        }
    }

    public function assignToTeam(Request $request, Team $team)
    {
        $request->validate([
            'player_id' => 'required|exists:players,id',
        ], [
            'player_id.required' => 'Selecteer een speler om toe te voegen.',
            'player_id.exists' => 'De geselecteerde speler bestaat niet.',
        ]);

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
