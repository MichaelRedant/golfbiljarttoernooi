<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Manche;
use App\Models\Team;
use App\Models\Player;
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
                'title' => $game->homeTeam->name . ' tegen ' . $game->awayTeam->name,
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
    $validatedData = $request->validate([
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

      // Verwerk scores voor elke speler...
    if (array_key_exists('players', $validatedData)) {
        // Verwerk scores voor elke speler...
        foreach ($validatedData['players'] as $playerId => $scores) {
            $player = Player::find($playerId);
            $mancheWinCount = 0;
            $mancheLossCount = 0;

            // Bepaal manche winsten en verliezen...
            if ($scores['manche_1_score'] > $scores['manche_2_score']) {
                $mancheWinCount++;
            } elseif ($scores['manche_1_score'] < $scores['manche_2_score']) {
                $mancheLossCount++;
            }

            // Controleer voor belle winst/verlies...
            if ($scores['belle_score'] === 1) {
                $mancheWinCount++;
            } elseif ($scores['belle_score'] === 2) {
                $mancheLossCount++;
            }

            // Werk de player statistieken bij...
            $player->increment('manches_won', $mancheWinCount);
            $player->increment('manches_lost', $mancheLossCount);

            // Bepaal of de speler de wedstrijd gewonnen of verloren heeft...
            // Dit is een vereenvoudigd voorbeeld, je moet de logica aanpassen aan jouw regels
            if ($game->home_team_id === $player->team_id && $validatedData['home_score'] > $validatedData['away_score']) {
                $player->increment('matches_won');
            } else {
                $player->increment('matches_lost');
            }

            // Sla de bijgewerkte spelergegevens op...
            $player->save();
        }
    }

    // Werk de game record bij met de totale scores
    $game->update([
        'home_score' => $validatedData['home_score'],
        'away_score' => $validatedData['away_score'],
        'matches_won' => $validatedData['matches_won'],
        'matches_lost' => $validatedData['matches_lost'],
        'manches_won' => $validatedData['manches_won'],
        'manches_lost' => $validatedData['matches_lost'],
    ]);

    $this->updatePlayerStats($game, $validatedData);


    $game->update($validatedData);
    $this->updateMatchStatistics($game, $validatedData);


    return redirect()->route('games.index')->with('success', 'Wedstrijd succesvol bijgewerkt.');
}

protected function updateMatchStatistics(Game $game, $validatedData)
{
    // Voorbeeld van hoe je kunt bepalen wie de winnaar is en de statistieken bijwerken
    foreach ($validatedData['players'] as $playerData) {
        $player = Player::find($playerData['player_id']);

        // Basislogica voor het bijwerken van spelersstatistieken
        // Dit moet worden aangepast op basis van je specifieke logica voor winnen/verliezen
        if ($playerData['is_winner']) {
            $player->matches_won++;
        } else {
            $player->matches_lost++;
        }

        $player->save();
    }

    // Bijwerken van teamstatistieken
    // Dit is vergelijkbaar en hangt af van hoe je de teamstatistieken bijhoudt
}

protected function updatePlayerStats($game, $validatedData) {
    // Voor elke speler die heeft deelgenomen aan de game
    foreach ($validatedData['players'] as $playerData) {
        $player = Player::find($playerData['player_id']);
        
        // Hier check je wie de winnaar van de manche is en verhoog je de win/loss counters
        // Dit is pseudocode, je zult de logica moeten schrijven gebaseerd op je formuliervelden en regels
        if ($game->isForfeit()) {
            $player->increment('matches_lost');
        } else {
            if ($playerData['is_winner']) {
                $player->increment('matches_won');
                $player->increment('manches_won', $playerData['manche_wins']);
            } else {
                $player->increment('matches_lost');
                $player->increment('manches_lost', $playerData['manche_losses']);
            }
        }
        $player->save();
    }
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
