<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Division;
use App\Models\Team;
use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PlayerController extends Controller
{
    public function index(Request $request)
{
    Log::info('Index method called');
    Log::info('Request data:', $request->all());

    try {
        $query = $request->input('query');
        $divisionId = $request->input('division_id');
        $teamId = $request->input('team_id');
        $sortOrder = $request->input('sort', 'asc');

        $divisions = Division::all();
        $teams = collect();
        $players = collect();

        if ($divisionId) {
            $teams = Team::whereHas('divisions', function ($query) use ($divisionId) {
                $query->where('divisions.id', $divisionId);
            })->get();

            $players = Player::with('team')
                ->whereHas('team.divisions', function ($query) use ($divisionId) {
                    $query->where('divisions.id', $divisionId);
                })
                ->orderBy('last_name', $sortOrder)
                ->orderBy('first_name', $sortOrder)
                ->get();

            if ($teamId) {
                $players = $players->filter(function ($player) use ($teamId) {
                    return $player->team_id == $teamId;
                });
            }

            if ($players->isNotEmpty()) {
                $divisionIds = $players->first()->team->divisions->pluck('id')->toArray() ?? [];
                $currentSeasonId = Season::latest('id')->first()->id;
                $standings = $divisionIds ? $this->calculatePlayerStandings($divisionIds, $currentSeasonId) : [];

                $players = $players->map(function ($player) use ($standings) {
                    $rank = array_search($player->id, array_column($standings, 'player_id')) + 1;
                    return (object) [
                        'id' => $player->id,
                        'name' => $player->first_name . ' ' . $player->last_name,
                        'team_name' => $player->team->name ?? 'Geen team',
                        'team_id' => $player->team->id ?? null,
                        'rank' => $rank,
                    ];
                });
            }
        }

        if ($query) {
            $players = Player::with('team')
                ->where('first_name', 'LIKE', "%{$query}%")
                ->orWhere('last_name', 'LIKE', "%{$query}%")
                ->orderBy('last_name', $sortOrder)
                ->orderBy('first_name', $sortOrder)
                ->get();

            if ($players->isNotEmpty()) {
                $divisionIds = $players->first()->team->divisions->pluck('id')->toArray() ?? [];
                $currentSeasonId = Season::latest('id')->first()->id;
                $standings = $divisionIds ? $this->calculatePlayerStandings($divisionIds, $currentSeasonId) : [];

                $players = $players->map(function ($player) use ($standings) {
                    $rank = array_search($player->id, array_column($standings, 'player_id')) + 1;
                    return (object) [
                        'id' => $player->id,
                        'name' => $player->first_name . ' ' . $player->last_name,
                        'team_name' => $player->team->name ?? 'Geen team',
                        'team_id' => $player->team->id ?? null,
                        'rank' => $rank,
                    ];
                });
            }
        }

        return view('players.index', compact('players', 'divisions', 'teams', 'sortOrder'));
    } catch (\Exception $e) {
        Log::error('Error fetching players: ' . $e->getMessage());
        return back()->withErrors('Er is een fout opgetreden bij het ophalen van de spelerslijst.');
    }
}


    public function create()
    {
        $divisions = Division::all();
        $teams = Team::all();
        return view('players.create', compact('divisions', 'teams'));
    }

    public function store(Request $request)
    {
        Log::info('Store method called');
        Log::info('Request data: ', $request->all());

        try {
            $validatedData = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'team_id' => 'nullable|exists:teams,id',
                'photo' => 'nullable|image|max:2048',
                'thumbnail' => 'nullable|image|max:2048',
            ]);

            $team = Team::find($validatedData['team_id']);
            $divisionId = $team ? $team->divisions->first()->id : null;
            $validatedData['division_id'] = $divisionId;

            if ($request->hasFile('photo')) {
                $validatedData['photo'] = $request->file('photo')->store('photos');
            }
            if ($request->hasFile('thumbnail')) {
                $validatedData['thumbnail'] = $request->file('thumbnail')->store('thumbnails');
            }

            Player::create($validatedData);

            if ($request->input('save_and_add_another')) {
                return redirect()->route('players.create')->with('success', 'Speler succesvol toegevoegd. Voeg een nieuwe speler toe.');
            }

            return redirect()->route('players.index')->with('success', 'Speler succesvol toegevoegd.');
        } catch (\Exception $e) {
            Log::error('Error storing player: ' . $e->getMessage());
            return back()->withErrors('Er is een fout opgetreden bij het opslaan van de speler.');
        }
    }

    public function show(Player $player, Request $request)
    {
        try {
            $imageUrl = Storage::url('photos/' . $player->photo);
            $seasons = Season::all();
            $currentSeasonId = $request->input('season_id', Season::latest('id')->first()->id);

            $teamGamesHome = $player->team->gamesHome()->where('season_id', $currentSeasonId)->whereNotNull('home_score')->whereNotNull('away_score')->get();
            $teamGamesAway = $player->team->gamesAway()->where('season_id', $currentSeasonId)->whereNotNull('home_score')->whereNotNull('away_score')->get();
            $teamGames = $teamGamesHome->merge($teamGamesAway);

            $gamesWon = $teamGames->reduce(function ($carry, $game) use ($player) {
                return $carry + (($game->winner_id == $player->team_id) ? 1 : 0);
            }, 0);
            $gamesLost = $teamGames->reduce(function ($carry, $game) use ($player) {
                return $carry + (($game->loser_id == $player->team_id) ? 1 : 0);
            }, 0);
            $gamesDraw = $teamGames->reduce(function ($carry, $game) {
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

            $standings = $this->calculatePlayerStandings($player->division_id, $currentSeasonId);
            $playerRank = array_search($player->id, array_column($standings, 'player_id')) + 1;

            return view('players.show', compact(
                'player',
                'seasons',
                'currentSeasonId',
                'gamesWon',
                'gamesLost',
                'gamesDraw',
                'matchesWon',
                'matchesLost',
                'imageUrl',
                'standings',
                'playerRank'
            ));
        } catch (\Exception $e) {
            Log::error('Error showing player: ' . $e->getMessage());
            return back()->withErrors('Er is een fout opgetreden bij het ophalen van de speler.');
        }
    }

    public function edit(Player $player)
    {
        $divisions = Division::all();
        $teams = Team::all();
        return view('players.edit', compact('player', 'divisions', 'teams'));
    }

    public function update(Request $request, Player $player)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'team_id' => 'nullable|exists:teams,id',
            'photo' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            if ($request->hasFile('photo')) {
                $image = $request->file('photo');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $thumbnailName = 'thumbnail_' . $imageName;
                $image->storeAs('public/photos', $imageName);
                $image->storeAs('public/thumbnails', $thumbnailName);
                Storage::delete(['public/photos/' . $player->photo, 'public/thumbnails/' . $player->thumbnail]);
                $player->photo = $imageName;
                $player->thumbnail = $thumbnailName;
            }

            $player->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'team_id' => $request->team_id,
                'photo' => $imageName ?? $player->photo,
                'thumbnail' => $thumbnailName ?? $player->thumbnail,
            ]);

            return redirect()->route('players.index')->with('success', 'Speler succesvol bijgewerkt.');
        } catch (\Exception $e) {
            Log::error('Error updating player: ' . $e->getMessage());
            return back()->withErrors('Er is een fout opgetreden bij het bijwerken van de speler.');
        }
    }

    public function getTeams(Request $request)
    {
        try {
            $divisionId = $request->division_id;
            Log::info('Fetching teams for division ID: ' . $divisionId);
            
            $teams = Team::where('division_id', $divisionId)->get();
            return response()->json($teams);
        } catch (\Exception $e) {
            Log::error('Error fetching teams: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch teams'], 500);
        }
    }

    protected function calculatePlayerStandings($divisionId, $currentSeasonId)
    {
        $players = Player::where('division_id', $divisionId)
            ->with(['team.gamesHome' => function ($query) use ($currentSeasonId) {
                $query->where('season_id', $currentSeasonId)->whereNotNull('home_score')->whereNotNull('away_score');
            }, 'team.gamesAway' => function ($query) use ($currentSeasonId) {
                $query->where('season_id', $currentSeasonId)->whereNotNull('home_score')->whereNotNull('away_score');
            }])
            ->get();

        $standings = [];
        foreach ($players as $player) {
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

            $points = $matchesWon * 3;

            $standings[] = [
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
        }

        usort($standings, function ($a, $b) {
            return $b['points'] <=> $a['points'];
        });

        return $standings;
    }

    public function getPlayersByTeam(Request $request)
    {
        try {
            $teamId = $request->query('team_id');
            Log::info('Fetching players for team ID: ' . $teamId);

            $players = Player::with('team', 'team.gamesHome', 'team.gamesAway')
                            ->where('team_id', $teamId)
                            ->get();

            if ($players->isEmpty()) {
                return response()->json([], 200);
            }

            $divisionId = $players->first()->team->division_id;
            $standings = $this->calculatePlayerStandings($divisionId, $request->season_id);

            $playersData = $players->map(function ($player) use ($standings) {
                $rank = array_search($player->id, array_column($standings, 'player_id')) + 1;
                return [
                    'id' => $player->id,
                    'name' => $player->first_name . ' ' . $player->last_name,
                    'team_name' => $player->team->name ?? 'Geen team',
                    'team_id' => $player->team->id ?? null,
                    'rank' => $rank,
                ];
            });

            return response()->json($playersData);
        } catch (\Exception $e) {
            Log::error('Error fetching players by team: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch players'], 500);
        }
    }

    public function playersBySeason($divisionId, $seasonId)
    {
        return Player::whereHas('games', function ($query) use ($seasonId) {
                       $query->where('season_id', $seasonId);
                   })
                   ->where('division_id', $divisionId)
                   ->get();
    }

    public function searchPlayers(Request $request)
    {
        $query = $request->query('query');

        if (empty($query)) {
            return response()->json([]);
        }

        try {
            $players = Player::with('team')
                             ->where('first_name', 'LIKE', "%{$query}%")
                             ->orWhere('last_name', 'LIKE', "%{$query}%")
                             ->get();

            if ($players->isEmpty()) {
                return response()->json([]);
            }

            $divisionId = $players->first()->team->division_id ?? null;
            $currentSeasonId = Season::latest('id')->first()->id;
            $standings = $divisionId ? $this->calculatePlayerStandings($divisionId, $currentSeasonId) : [];

            $playersData = $players->map(function ($player) use ($standings) {
                $rank = array_search($player->id, array_column($standings, 'player_id')) + 1;
                return [
                    'id' => $player->id,
                    'name' => $player->first_name . ' ' . $player->last_name,
                    'team_name' => $player->team->name,
                    'team_id' => $player->team->id,
                    'rank' => $rank,
                ];
            });

            return response()->json($playersData);
        } catch (\Exception $e) {
            Log::error('Error searching players: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to search players'], 500);
        }
    }

    public function getTeamsByDivision($divisionId)
    {
        try {
            Log::info('Fetching teams for division ID: ' . $divisionId);
            
            $teams = Team::where('division_id', $divisionId)->get();
            return response()->json($teams);
        } catch (\Exception $e) {
            Log::error('Error fetching teams: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch teams'], 500);
        }
    }

    public function removeFromTeam(Player $player, Team $team)
    {
        $player->team_id = null;
        $player->save();

        return redirect()->back()->with('success', 'Speler verwijderd uit team.');
    }

    public function moveToTeam(Request $request, Player $player)
    {
        $request->validate([
            'new_team_id' => 'required|exists:teams,id'
        ]);

        $player->team_id = $request->new_team_id;
        $player->save();

        return redirect()->back()->with('success', 'Speler succesvol verplaatst naar ander team.');
    }

    public function destroy(Player $player)
    {
        Storage::delete(['public/photos/' . $player->photo, 'public/thumbnails/' . $player->thumbnail]);
        $player->delete();
        return redirect()->route('players.index')->with('success', 'Speler succesvol verwijderd.');
    }
}
