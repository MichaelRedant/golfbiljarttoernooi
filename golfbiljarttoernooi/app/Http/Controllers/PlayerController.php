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
        return view('players.index', compact('players'));
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
        return view('players.show', compact('player'));
    }

    public function edit(Player $player)
    {
        $divisions = Division::all();
        return view('players.edit', compact('player', 'divisions'));
    }

    public function update(Request $request, Player $player)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'team_id' => 'required|string|max:255',
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

    public function destroy(Player $player)
    {
        Storage::delete(['public/photos/' . $player->photo, 'public/thumbnails/' . $player->thumbnail]);
        $player->delete();
        return redirect()->route('players.index')->with('success', 'Speler succesvol verwijderd.');
    }

    public function getTeams(Request $request)
    {
        $teams = Team::where('division_id', $request->division_id)->get();
        return response()->json($teams);
    }
}

