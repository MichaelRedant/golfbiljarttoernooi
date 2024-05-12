<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Game;
use App\Models\Team;
use App\Models\Manche;
use App\Models\Player;
use App\Models\Season;
use App\Models\Division;
use Illuminate\Http\Request;

class GameController extends Controller
{
    /* public function index(Request $request)
    {
        $seasons = Season::all();
    
        // Vind het laatste seizoen en haal het id op indien beschikbaar.
        $latestSeason = Season::latest('id')->first();
        $currentSeasonId = $request->input('season_id', optional($latestSeason)->id);
    
        $gamesByDate = collect();
        if ($currentSeasonId) {
            $gamesByDate = Game::with(['homeTeam', 'awayTeam'])
                               ->where('season_id', $currentSeasonId)
                               ->orderBy('date', 'asc')
                               ->get()
                               ->groupBy('date');
        }
    
        return view('games.index', compact('gamesByDate', 'seasons', 'currentSeasonId'));
    }

    public function edit(Game $game)
{
    $teams = Team::all();  // Zorg dat je de Team model hebt geladen via use App\Models\Team;

    // Gebruik 'authorize' om te controleren of de gebruiker de wedstrijd mag bewerken
    $this->authorize('update', $game);

    return view('games.edit', compact('game', 'teams'));
} */

public function index(Request $request)
{
    $divisionId = $request->input('division_id');
    $division = Division::find($divisionId);
    $currentSeasonId = $request->input('season_id', Season::latest('id')->first()->id);

    if (!$division) {
        return redirect()->route('home')->withErrors('Divisie niet gevonden');
    }

    $upcomingGames = Game::where('division_id', $divisionId)->where('date', '>=', Carbon::now())->orderBy('date', 'asc')->get();
    $pastGames = Game::where('division_id', $divisionId)->where('date', '<', Carbon::now())->orderBy('date', 'desc')->get();
    $standings = $this->calculateStandings($divisionId);  // Zorg ervoor dat deze functie de standen correct berekent
    $nextMatchday = $upcomingGames->first()->date ?? 'Geen wedstrijden gepland';

    return view('games.index', compact('division', 'currentSeasonId', 'upcomingGames', 'standings', 'pastGames', 'nextMatchday'));
}

public function create(Request $request)
{
    $divisions = Division::all();
    $teams = Team::all();
    $seasons = Season::all();  // Retrieve all seasons for dropdown

    // Retrieve division and season IDs from request, fallback to defaults
    $selectedDivisionId = $request->input('division_id', $divisions->first()->id ?? null);
    $selectedSeasonId = $request->input('season_id', Season::latest('id')->first()->id);

    // Send selected IDs to the view to pre-select in dropdowns
    return view('games.create', compact('divisions', 'teams', 'seasons', 'selectedDivisionId', 'selectedSeasonId'));
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

    public function showDashboard()
{
    // Haal alle divisies op
    $divisions = Division::all();
    // Haal alle seizoenen op voor de dropdown
    $seasons = Season::all();

    // Haal het laatste (meest recente) seizoen op
    $currentSeason = Season::latest('id')->first();

    // Geef divisies en het huidige seizoen door aan de dashboard view
    return view('dashboard', compact('divisions', 'currentSeason', 'seasons'));
}

public function showGamesForDivisionAndSeason(Request $request, $division_id, $season_id = null)
{
    $division = Division::findOrFail($division_id);
    $seasons = Season::all();
    
    // Als geen seizoen_id is gegeven, gebruik het meest recente seizoen
    $season_id = $season_id ?? Season::latest('id')->first()->id;
    $season = Season::findOrFail($season_id);

    $games = Game::where('division_id', $division_id)
                 ->where('season_id', $season_id)
                 ->where('date', '>=', Carbon::now()) // Toont alleen toekomstige games
                 ->orderBy('date', 'asc')
                 ->get();

    return view('games.list', compact('games', 'division', 'season', 'seasons'));
}


public function store(Request $request)
{
    $validatedData = $request->validate([
        'home_team_id' => 'nullable|exists:teams,id',
        'away_team_id' => 'nullable|exists:teams,id',
        'bye_team_id' => 'nullable|exists:teams,id',
        'date' => 'required|date',
        'division_id' => 'required|exists:divisions,id',
        'season_id' => 'required|exists:seasons,id'
    ]);

    $game = new Game();
    $game->home_team_id = $validatedData['home_team_id'] ?: null; // Stel in op null als geen team geselecteerd
    $game->away_team_id = $validatedData['away_team_id'] ?: null; // Stel in op null als geen team geselecteerd
    $game->bye_team_id = $validatedData['bye_team_id'] ?: null;  // Stel in op null als geen Bye geselecteerd
    $game->date = $validatedData['date'];
    $game->division_id = $validatedData['division_id'];
    $game->season_id = $validatedData['season_id'];
    $game->save();

    return redirect()->route('games.index', ['division_id' => $validatedData['division_id']])
                     ->with('success', 'Wedstrijd succesvol aangemaakt!');
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
    $game->load([
        'homeTeam' => function ($query) {
            $query->with(['club.teams.players']);
        },
        'awayTeam' => function ($query) {
            $query->with(['club.teams.players']);
        },
        'manches', 
        'belles'
    ]);

    // Verzamel alle spelers van teams die tot dezelfde club behoren als het thuisteam
    $homeTeamPlayers = $game->homeTeam->club->teams->flatMap(function ($team) {
        return $team->players;
    });

    // Verzamel alle spelers van teams die tot dezelfde club behoren als het uitteam
    $awayTeamPlayers = $game->awayTeam->club->teams->flatMap(function ($team) {
        return $team->players;
    });

    return view('games.match_form', compact('game', 'homeTeamPlayers', 'awayTeamPlayers'));
}

public function play(Game $game)
{
    // Controleer of de gebruiker is geautoriseerd om deze actie uit te voeren
    $this->authorize('update', $game);

    // Implementeer de logica om een wedstrijd te spelen
    return view('games.play', compact('game'));
}

public function show(Game $game)
{
    $game->load('homeTeam', 'awayTeam', 'manches');  // Ensure 'manches' are being loaded
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
            'date' => 'required|date', // Toevoegen van datumvalidatie
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
    
        // Update the game details including the date
        $game->update([
            'home_score' => $homeWins,
            'away_score' => $awayWins,
            'date' => $data['date'] // Vergeet niet de datum bij te werken
        ]);
    
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