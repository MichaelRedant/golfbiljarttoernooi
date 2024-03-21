<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Manche;
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
    // Verwijder eerst gerelateerde records om buitenlandse sleutelbeperkingen te respecteren
    Manche::query()->delete(); // Verwijder alle manches (ervan uitgaande dat je een Manche model hebt)

    Game::query()->delete(); // Verwijdert alle records uit de 'games' tabel.

    return redirect()->route('games.index')->with('success', 'Kalender succesvol verwijderd!');
}




    public function show(Game $game)
    {
        return view('games.show', compact('game'));
    }

    protected function updatePlayerScores(Game $game, array $playersData)
    {
        // Voorbereiden van de data voor de pivot tabel
        $pivotData = [];
        foreach ($playersData as $playerId => $data) {
            $pivotData[$playerId] = [
                'manche_1_score' => $data['manche_1_score'] ?? 0,
                'manche_2_score' => $data['manche_2_score'] ?? 0,
                'belle_score' => $data['belle_score'] ?? 0,
                'is_belle_winner' => isset($data['belle_winner']) && $data['belle_winner'] == $playerId
            ];
        }
    
        // Bijwerken van de pivot tabel met nieuwe scores
        $game->players()->sync($pivotData);
    }

    
    public function update(Request $request, Game $game)
{
    $request->validate([
        'home_team_id' => 'exists:teams,id',
        'away_team_id' => 'exists:teams,id',
        'home_forfeit' => 'sometimes|boolean',
        'away_forfeit' => 'sometimes|boolean',
        'home_score' => 'nullable|integer',
        'away_score' => 'nullable|integer',
        'players' => 'array',
        'players.*.player_id' => 'exists:players,id',
        'players.*.manche_1_score' => 'required|integer|min:0',
        'players.*.manche_2_score' => 'required|integer|min:0',
        'players.*.belle_score' => 'nullable|integer|min:0',
        'players.*.is_belle_winner' => 'required|boolean',
    ]);

    //Na validatie
    // $game->home_team_id = $validatedData['home_team_id'];
    // $game->away_team_id = $validatedData['away_team_id'];
    // $game->home_forfeit = $request->has('home_forfeit') ? 1 : 0;
    // $game->away_forfeit = $request->has('away_forfeit') ? 1 : 0;
    // $game->home_score = $validatedData['home_score'] ?? 0; 
    // $game->away_score = $validatedData['away_score'] ?? 0;

    $game->update([
        'home_score' => $request->home_score,
        'away_score' => $request->away_score,
    ]);

    // Sla de bijgewerkte game op
    //$game->save();

    // Verwijder eerst alle bestaande speler relaties om ze te herstellen
    // $game->players()->detach();

    // Bijwerken van scores en details van spelers
    // foreach ($validatedData['players'] as $playerData) {
    //     $game->players()->attach($playerData['player_id'], [
    //         'manche_1_score' => $playerData['manche_1_score'],
    //         'manche_2_score' => $playerData['manche_2_score'],
    //         'belle_score' => $playerData['belle_score'] ?? null, // Maak nullable als geen score voor belle
    //         'is_belle_winner' => $playerData['is_belle_winner'],
    //     ]);
    // }

    return redirect()->route('games.index')->with('success', 'Wedstrijd succesvol bijgewerkt.');
}

    
    
    protected function updateManches(Game $game, array $manchesData)
    {
    // Eerst verwijderen we alle bestaande relaties om ze te herstellen
    // Dit is een eenvoudige aanpak maar niet de meest efficiënte voor grote datasets.
    // Overweeg een meer geavanceerde logica voor het bijwerken van bestaande records.
    $game->players()->detach();

    foreach ($manchesData as $playerId => $scores) {
        // Hier gaan we ervan uit dat $manchesData is georganiseerd per speler ID,
        // met scores voor elke manche en de belle.
        $game->players()->attach($playerId, [
            'manche_1_score' => $scores['manche_1_score'] ?? 0,
            'manche_2_score' => $scores['manche_2_score'] ?? 0,
            'belle_score' => $scores['belle_score'] ?? 0,
            // Bepaal op basis van scores of de speler de belle heeft gewonnen
            'is_belle_winner' => isset($scores['belle_score']) && $scores['belle_score'] > 0
        ]);
    }
}
    
    protected function updateBelles(Game $game, array $bellesData)
    {
        // Aangezien een game maximaal één belle kan hebben, overwegen we de bestaande te overschrijven.
        // Eerst verwijderen we de bestaande belle-gegevens voor deze game, indien aanwezig.
        $game->belles()->delete();
    

        foreach ($bellesData as $belle) {
            $game->belles()->create([
                'player_id' => $belle['player_id'],
                'score' => $belle['score'],
                // Aannemende dat er een 'is_winner' veld is om aan te geven wie de belle heeft gewonnen
                'is_winner' => $belle['is_winner'] ?? false,
                // Voeg andere relevante belle-gerelateerde velden toe zoals benodigd
            ]);
        }
    }
    
public function editForm(Game $game)
{
    $game->load('homeTeam.players', 'awayTeam.players', 'manches', 'belles');

    $homeTeamPlayers = $game->homeTeam->players;
    $awayTeamPlayers = $game->awayTeam->players;

    return view('games.match_form', compact('game', 'homeTeamPlayers', 'awayTeamPlayers'));
}


    public function destroy(Game $game)
    {
        $game->delete();
        return redirect()->route('games.index');
    }
}
