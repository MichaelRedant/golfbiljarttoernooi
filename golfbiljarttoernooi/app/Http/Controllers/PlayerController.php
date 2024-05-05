<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Division;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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


public function show(Player $player)
{
    // Ensure the image URL is generated correctly.
    $imageUrl = Storage::url('photos/'.$player->photo);
    return view('players.show', compact('player', 'imageUrl'));
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

    public function calculatePlayerStandings($divisionId)
{
    // Haal alle spelers op in de opgegeven divisie met hun games en scores, inclusief teamgegevens
    $players = Player::with(['team', 'games.manches'])
                     ->where('division_id', $divisionId)
                     ->get();

    $standings = [];
    foreach ($players as $player) {
        $standings[] = [
            'player_id' => $player->id,
            'player_name' => $player->first_name . ' ' . $player->last_name,
            'team_id' => $player->team->id,
            'team_name' => $player->team->name,
            'games_won' => $player->matches_won, // Deze waarden worden nu rechtstreeks uit het model gehaald
            'games_lost' => $player->matches_lost,
            'manches_won' => $player->manches_won,
            'manches_lost' => $player->manches_lost,
            'points' => $player->manches_won - $player->manches_lost, // Punten kunnen een simpele berekening zijn
        ];
    }

    usort($standings, function ($a, $b) {
        return $b['points'] <=> $a['points']; // Sorteren op punten, hoog naar laag
    });

    return view('players.standings', ['standings' => $standings, 'divisionId' => $divisionId]);
}



public function getPlayersByTeam(Request $request)
{
    $teamId = $request->query('team_id');
    $players = Player::with(['games'])->where('team_id', $teamId)->get();

    $playersData = $players->map(function ($player) {
        return [
            'id' => $player->id,
            'name' => $player->first_name . ' ' . $player->last_name,
            'games_played' => $player->games->count(),
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

