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
    public function index()
    {
        $players = Player::all();
        $divisions = Division::all(); // Haal alle divisies op
        return view('players.index', compact('players', 'divisions'));
    }

    public function create()
    {
        $divisions = Division::all();
        $teams = Team::all(); 
        return view('players.create', compact('divisions', 'teams'));
    }

    public function store(Request $request)
{
    $request->validate([
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'team_id' => 'required|exists:teams,id',
        'division_id' => 'required|exists:divisions,id',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Maak 'photo' nullable en pas validatie aan
    ]);

    // Handle file upload
    if ($request->hasFile('photo')) {
        $image = $request->file('photo');
        $imageName = time() . '.' . $image->getClientOriginalExtension();
        $thumbnailName = 'thumbnail_' . $imageName;
        $image->storeAs('public/photos', $imageName);
        $image->storeAs('public/thumbnails', $thumbnailName);
    } else {
        $imageName = null; // Accepteer null waarden
        $thumbnailName = null; // Accepteer null waarden
    }

    Player::create([
        'first_name' => $request->first_name,
        'last_name' => $request->last_name,
        'team_id' => $request->team_id,
        'division_id' => $request->division_id,
        'photo' => $imageName, // Kan null zijn
        'thumbnail' => $thumbnailName, // Kan null zijn
    ]);

    return redirect()->route('players.index')->with('success', 'Speler succesvol toegevoegd.');
}


public function show(Player $player, Request $request)
{
    $imageUrl = Storage::url('photos/' . $player->photo);
    $seasons = Season::all();
    $currentSeasonId = $request->input('season_id', Season::latest('id')->first()->id);

    // Load games for the player's team
    $teamGamesHome = $player->team->gamesHome()->where('season_id', $currentSeasonId)->whereNotNull('home_score')->whereNotNull('away_score')->get();
    $teamGamesAway = $player->team->gamesAway()->where('season_id', $currentSeasonId)->whereNotNull('home_score')->whereNotNull('away_score')->get();
    $teamGames = $teamGamesHome->merge($teamGamesAway);

    // Calculate team-level statistics
    $gamesWon = $teamGames->reduce(function ($carry, $game) use ($player) {
        return $carry + (($game->winner_id == $player->team_id) ? 1 : 0);
    }, 0);
    $gamesLost = $teamGames->reduce(function ($carry, $game) use ($player) {
        return $carry + (($game->loser_id == $player->team_id) ? 1 : 0);
    }, 0);
    $gamesDraw = $teamGames->reduce(function ($carry, $game) {
        return $carry + (($game->home_score === $game->away_score) ? 1 : 0);
    }, 0);

    // Calculate player-specific match performance
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

    // Calculate standings for the player's division and season
    $standings = $this->calculatePlayerStandings($player->division_id, $currentSeasonId);

    // Find player's rank
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
            'team_id' => 'required|exists:teams,id',
            'division_id' => 'required|exists:divisions,id',
            'photo' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
        ]);

        // Handle file upload
        if ($request->hasFile('photo')) {
            $image = $request->file('photo');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $thumbnailName = 'thumbnail_' . $imageName;
            $image->storeAs('public/photos', $imageName);
            $image->storeAs('public/thumbnails', $thumbnailName);
            // Remove old photo and thumbnail
            Storage::delete(['public/photos/' . $player->photo, 'public/thumbnails/' . $player->thumbnail]);
            $player->photo = $imageName;
            $player->thumbnail = $thumbnailName;
        }

        $player->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'team_id' => $request->team_id,
            'division_id' => $request->division_id,
            'photo' => $imageName ?? $player->photo, // Keep the old photo if no new one uploaded
            'thumbnail' => $thumbnailName ?? $player->thumbnail, // Keep the old thumbnail if no new one uploaded
        ]);

        return redirect()->route('players.index')->with('success', 'Speler succesvol bijgewerkt.');
    }

    public function getTeams(Request $request)
    {
        $teams = Team::where('division_id', $request->division_id)->get();
        return response()->json($teams);
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
    $teamId = $request->query('team_id');
    $players = Player::with('team', 'team.gamesHome', 'team.gamesAway')
                      ->where('team_id', $teamId)
                      ->get();

    $divisionId = $players->first()->team->division_id;
    $standings = $this->calculatePlayerStandings($divisionId, $request);

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
    $players = Player::with('team')
                     ->where('first_name', 'LIKE', "%{$query}%")
                     ->orWhere('last_name', 'LIKE', "%{$query}%")
                     ->get();

    $divisionId = $players->first()->team->division_id ?? null;
    $standings = $divisionId ? $this->calculatePlayerStandings($divisionId, $request) : [];

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
}

public function removeFromTeam(Player $player, Team $team)
{
    // Verwijder de speler uit het team
    $player->team_id = null; // Stel in op null of een andere standaardwaarde
    $player->save();

    return redirect()->back()->with('success', 'Speler verwijderd uit team.');
}

// Je zou ook een methode kunnen toevoegen om spelers te verzetten naar een ander team
public function moveToTeam(Request $request, Player $player)
{
    // Valideer dat het nieuwe team_id bestaat
    $request->validate([
        'new_team_id' => 'required|exists:teams,id'
    ]);

    // Update de speler met het nieuwe team_id
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

