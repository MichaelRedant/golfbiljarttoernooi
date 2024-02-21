<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index()
    {
        $games = Game::with(['homeTeam', 'awayTeam'])->orderBy('date', 'asc')->get();
    $gamesByDate = $games->groupBy('date'); // Dit groepeert games per datum
    return view('games.index', compact('gamesByDate'));
    }

    public function calendarData()
    {
        $games = Game::all();
        $events = [];
        foreach ($games as $game) {
            $events[] = [
                'title' => $game->homeTeam->name . ' vs ' . $game->awayTeam->name,
                'start' => $game->date,
                'url'   => route('games.show', $game->id),
            ];
        }
        return response()->json($events);
    }

    public function generateMatches()
{
    $teams = Team::all();
    $numTeams = $teams->count();
    $totalRounds = $numTeams - 1; // Round-robin systeem
    $matchesPerRound = intdiv($numTeams, 2);
    $matchDate = Carbon::now()->next('Saturday');

    $schedule = [];

    // Double round-robin systeem: elk team speelt twee keer tegen elk ander team, een keer thuis en een keer uit.
    for ($i = 0; $i < $totalRounds * 2; $i++) {
        for ($j = 0; $j < $matchesPerRound; $j++) {
            // Bereken home en away teams voor deze match
            $home = ($i + $j) % $numTeams;
            $away = ($i + $numTeams - $j) % $numTeams;

            if ($home != $away) { // Voorkom dat een team tegen zichzelf speelt
                $schedule[] = [
                    'home_team_id' => $teams[$home]->id,
                    'away_team_id' => $teams[$away]->id,
                    'date' => $matchDate->copy()->addWeeks($i)->format('Y-m-d')
                ];
            }
        }
    }

    // Maak alle geplande games aan in de database
    foreach ($schedule as $gameData) {
        Game::create($gameData);
    }

    return redirect()->route('games.index')->with('success', 'Wedstrijden succesvol gegenereerd!');
}


    
    private function rotateTeams($teams)
{
    $teamsArray = $teams->toArray();
    $firstTeam = array_shift($teamsArray); // Haal het eerste team eruit
    $teamsArray[] = array_pop($teamsArray); // Plaats het laatste team voor het laatste
    array_unshift($teamsArray, $firstTeam); // Zet het eerste team terug op de eerste plaats
    return collect($teamsArray); // Zet het weer om naar een collectie
}

    

public function clearCalendar()
{
    Game::truncate(); // Dit verwijdert alle records uit de 'games' tabel.
    return redirect()->route('games.index')->with('success', 'Kalender succesvol verwijderd!');
}



    public function show(Game $game)
    {
        return view('games.show', compact('game'));
    }

    public function editForm(Game $game)
{
    $homeTeamPlayers = $game->homeTeam->players;
    $awayTeamPlayers = $game->awayTeam->players;

    return view('games.match_form', compact('game', 'homeTeamPlayers', 'awayTeamPlayers'));
}



public function update(Request $request, Game $game)
{
    // Valideer de ingevoerde gegevens
    $data = $request->validate([
        'home_players' => 'required|array',
        'home_players.*' => 'exists:players,id',
        'home_captain' => 'required|exists:players,id',
        'home_reserve' => 'required|exists:players,id',
        'away_players' => 'required|array',
        'away_players.*' => 'exists:players,id',
        'scores' => 'required|array',
        'away_captain' => 'required|exists:players,id',
        'away_reserve' => 'required|exists:players,id',
        'manche_scores' => 'required|array',
        'manche_scores.*' => 'string',
        'belle_score' => 'nullable|string'
    ]);

    // Werk de game bij met de nieuwe informatie
    // Dit hangt af van hoe je je database en relaties hebt ingesteld
    $game->update([
        // Voeg hier eventuele velden toe die direct bij de Game horen
    ]);

    // Update spelers en scores - afhankelijk van je databaseontwerp
    // Dit is een voorbeeld en moet worden aangepast aan je specifieke behoeften
    $game->homePlayers()->sync($data['home_players']);
    $game->awayPlayers()->sync($data['away_players']);

    // Werk de scores bij - afhankelijk van je ontwerp
    // Dit is een voorbeeld; je moet de logica aanpassen aan je database
    $game->manches()->delete(); // Verwijder oude manche records als dat nodig is
    
    foreach ($data['manche_scores'] as $score) {
        // Voeg nieuwe manche records toe
        $game->manches()->create(['score' => $score]);
    }

    if ($data['belle_score']) {
        // Update of voeg de belle score toe
        $game->belle()->create(['score' => $data['belle_score']]);
    }

    $homeWins = 0;
    $awayWins = 0;
    foreach ($data['scores'] as $score) {
        if ($score['winner'] == 'home') {
            $homeWins++;
        } elseif ($score['winner'] == 'away') {
            $awayWins++;
        }
    }

    // Werk de wedstrijd bij met de nieuwe scores
    $game->update([
        'home_score' => $homeWins, // Zorg ervoor dat je model deze velden accepteert
        'away_score' => $awayWins,
        // Voeg andere velden toe die je wilt bijwerken
    ]);
    $game->home_score = $request->input('home_score');
    $game->away_score = $request->input('away_score');
    $game->save(); // Opslaan van de bijgewerkte scores

    return redirect()->route('games.index')->with('success', 'Wedstrijd succesvol bijgewerkt!');
}


    public function destroy(Game $game)
    {
        $game->delete();
        return redirect()->route('games.index');
    }
}
