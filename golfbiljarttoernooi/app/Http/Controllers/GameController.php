<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Game;
use App\Models\Team;
use App\Models\Manche;
use App\Models\Player;
use App\Models\Season;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index(Request $request)
{
    // Ophalen van alle seizoenen voor de dropdown.
    $seasons = Season::all();

    // Vind het laatste seizoen en haal het id op indien beschikbaar.
    $latestSeason = Season::latest('id')->first();
    $currentSeasonId = $latestSeason ? $latestSeason->id : null;

    // Ophalen van games die gesorteerd zijn op datum en gegroepeerd op de datum, rekening houdend met het geselecteerde seizoen.
    if ($currentSeasonId) {
        $gamesByDate = Game::with(['homeTeam', 'awayTeam'])
                           ->where('season_id', $currentSeasonId)
                           ->orderBy('date', 'asc')
                           ->get()
                           ->groupBy('date');
    } else {
        // Als er geen seizoenen zijn, zorg ervoor dat er een lege collectie wordt teruggestuurd.
        $gamesByDate = collect();
    }

    // Terugsturen van de data naar de view met de games gegroepeerd op datum, de lijst van seizoenen en het ID van het huidige seizoen.
    return view('games.index', compact('gamesByDate', 'seasons', 'currentSeasonId'));
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

    public function store(Request $request)
{
    $data = $request->validate([
        'home_team_id' => 'required|exists:teams,id',
        'away_team_id' => 'required|exists:teams,id',
        // Overige validatie
    ]);

    $game = new Game($data);
    $game->season_id = Season::latest()->first()->id; // Wijs het meest recente seizoen toe
    $game->save();

    return redirect()->route('games.index')->with('success', 'Wedstrijd succesvol toegevoegd!');
}

public function generateMatches()
{
    $teams = Team::all();
    $numTeams = $teams->count();
    $totalRounds = $numTeams - 1;
    $matchesPerRound = intdiv($numTeams, 2);
    $matchDate = Carbon::now()->next('Saturday');
    $currentSeason = Season::latest()->first()->id; // Haal het huidige seizoen op

    $schedule = [];

    for ($i = 0; $i < $totalRounds * 2; $i++) {
        for ($j = 0; $j < $matchesPerRound; $j++) {
            $home = ($i + $j) % $numTeams;
            $away = ($i + $numTeams - $j) % $numTeams;
            if ($home != $away) {
                $schedule[] = [
                    'home_team_id' => $teams[$home]->id,
                    'away_team_id' => $teams[$away]->id,
                    'season_id' => $currentSeason,
                    'date' => $matchDate->copy()->addWeeks($i)->format('Y-m-d')
                ];
            }
        }
    }

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


public function editForm(Game $game)
{
    $game->load(['homeTeam.players', 'awayTeam.players', 'manches', 'belles']);

    $homeTeamPlayers = $game->homeTeam->players;
    $awayTeamPlayers = $game->awayTeam->players;
    

    return view('games.match_form', compact('game', 'homeTeamPlayers', 'awayTeamPlayers'));
    
}

public function show(Game $game)
{
    $game->load('homeTeam', 'awayTeam', 'manches');

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
    $data = $request->validate([
        'home_team_id' => 'required|exists:teams,id',
        'away_team_id' => 'required|exists:teams,id',
        'scores' => 'required|array',
        'scores.*.home_player' => 'required|exists:players,id',
        'scores.*.away_player' => 'required|exists:players,id',
        'scores.*.1M' => 'required|integer',
        'scores.*.2M' => 'required|integer',
        'scores.*.Belle' => 'nullable|integer'
    ]);

    $homeWins = 0;
    $awayWins = 0;

    foreach ($data['scores'] as $i => $score) {
        $matchResult = $this->calculateMatchResult($score);
        if ($matchResult == 1) {
            $homeWins++;
        } elseif ($matchResult == 2) {
            $awayWins++;
        }

        Manche::updateOrCreate(
            [
                'game_id' => $game->id,
                'player1_id' => $score['home_player'],
                'player2_id' => $score['away_player'],
            ],
            [
                'score1' => $score['1M'],
                'score2' => $score['2M'],
                'belle_score' => $score['Belle'] ?? null,
                'winner_id' => $matchResult == 1 ? $score['home_player'] : ($matchResult == 2 ? $score['away_player'] : null)
            ]
        );
    }

    // Update game scores based on the match results
    $game->home_score = $homeWins;
    $game->away_score = $awayWins;
    $game->save();

    $this->updatePlayerStats($game);

    return redirect()->route('games.show', $game->id)->with('success', 'Game updated successfully.');
}

protected function updatePlayerStats(Game $game)
{
    $manches = Manche::where('game_id', $game->id)->get();

    foreach ($manches as $manche) {
        $player1 = Player::find($manche->player1_id);
        $player2 = Player::find($manche->player2_id);

        // Update statistics for player1
        if ($manche->winner_id == $manche->player1_id) {
            $player1->increment('matches_won');
        } else {
            $player1->increment('matches_lost');
        }

        $player1->increment('manches_won', ($manche->score1 > $manche->score2) ? 1 : 0);
        $player1->increment('manches_lost', ($manche->score1 < $manche->score2) ? 1 : 0);

        // Update statistics for player2
        if ($manche->winner_id == $manche->player2_id) {
            $player2->increment('matches_won');
        } else {
            $player2->increment('matches_lost');
        }

        $player2->increment('manches_won', ($manche->score2 > $manche->score1) ? 1 : 0);
        $player2->increment('manches_lost', ($manche->score2 < $manche->score1) ? 1 : 0);
    }
}


protected function calculateMatchResult($score)
{
    $homePoints = $score['1M'] == 1 ? 1 : 0;
    $awayPoints = $score['2M'] == 2 ? 1 : 0;

    if ($homePoints == $awayPoints) {
        // Belle decides
        return $score['Belle'] ?? null;
    }

    return $homePoints > $awayPoints ? 1 : 2;
}

    
    
    function updateManches(Game $game, array $manchesData)
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
    
     function updateBelles(Game $game, array $bellesData)
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
    
    



     function destroy(Game $game)
    {
        $game->delete();
        return redirect()->route('games.index');
    }
}