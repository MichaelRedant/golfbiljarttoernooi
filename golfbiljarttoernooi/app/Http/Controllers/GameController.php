<?php

// app/Http/Controllers/GameController.php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index()
    {
        $games = Game::all();
        return view('games.index', compact('games'));
    }

    public function calendarData()
{
    // Haal wedstrijden op en converteer deze naar een formaat dat FullCalendar kan gebruiken
    $games = Game::all(); // Pas dit aan om overeen te komen met je eigen model logica

    $events = [];
    foreach ($games as $game) {
        $events[] = [
            'title' => $game->homeTeam->name . ' vs ' . $game->awayTeam->name,
            'start' => $game->match_date, // Zorg ervoor dat je een 'match_date' veld hebt in je games tabel
            'url'   => route('games.show', $game->id), // Maak een link naar de game detailpagina
            // Voeg meer eigenschappen toe zoals nodig
        ];
    }

    return response()->json($events);
}


    public function show(Game $game)
    {
        return view('games.show', compact('game'));
    }

    public function edit(Game $game)
    {
        return view('games.edit', compact('game'));
    }

    public function update(Request $request, Game $game)
    {
        $game->update($request->all());
        return redirect()->route('games.index');
    }

    public function destroy(Game $game)
    {
        $game->delete();
        return redirect()->route('games.index');
    }
}
