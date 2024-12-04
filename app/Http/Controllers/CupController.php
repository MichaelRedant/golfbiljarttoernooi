<?php

namespace App\Http\Controllers;

use App\Models\Cup;
use App\Models\Team;
use App\Models\Season;
use App\Models\CupGame;
use App\Models\CupRound;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;

class CupController extends Controller
{
    public function index(Request $request)
{
    // Filter de cups per divisie
    $divisionId = $request->query('division_id'); // Divisie kan worden meegegeven in de query string

    // Haal alle cups op, gefilterd per divisie als die is ingesteld
    if ($divisionId) {
        $cups = Cup::where('division_id', $divisionId)->with(['season', 'division'])->get();
    } else {
        $cups = Cup::with(['season', 'division'])->get(); // Haal alle cups op als er geen divisie is gekozen
    }

    return view('cups.index', compact('cups'));
}


    // 2. Create a new Cup
    public function create()
    {
        // Haal alle seizoenen en divisies op om toe te wijzen aan de beker
        $seasons = Season::all();
        $divisions = Division::all();
        return view('cups.create', compact('seasons', 'divisions'));
    }

    public function store(Request $request)
{
    Log::info('Start storing a new cup', ['request' => $request->all()]);

    // Valideer het verzoek
    try {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'season_id' => 'required|exists:seasons,id',
            'division_id' => 'required|exists:divisions,id',
        ]);

        Log::info('Validation passed', ['validated' => $validated]);

        // Maak de nieuwe beker aan
        $cup = Cup::create([
            'name' => $validated['name'],
            'season_id' => $validated['season_id'],
            'division_id' => $validated['division_id'],
        ]);

        Log::info('Cup successfully created', ['cup' => $cup]);

        // Voeg de rondes toe aan de beker
        $this->createCupRounds($cup);

        return redirect()->route('cups.index')->with('success', 'Beker succesvol aangemaakt.');
    } catch (ValidationException $e) {
        Log::error('Validation failed', ['errors' => $e->errors()]);
        return redirect()->back()->withErrors($e->errors());
    } catch (\Exception $e) {
        Log::error('Error storing cup', ['error' => $e->getMessage()]);
        return redirect()->back()->withErrors('Er is een fout opgetreden bij het aanmaken van de beker. Probeer het opnieuw.');
    }
}

// Functie om de rondes aan te maken
protected function createCupRounds(Cup $cup)
{
    // Creëer de 1/8 finale (heen en terug)
    $cup->rounds()->create([
        'round_name' => '1/8 Finale Heenwedstrijd',
        'start_date' => now(),
        'end_date' => now()->addWeeks(1),
    ]);
    $cup->rounds()->create([
        'round_name' => '1/8 Finale Terugwedstrijd',
        'start_date' => now()->addWeeks(1),
        'end_date' => now()->addWeeks(2),
    ]);

    // Creëer de 1/4 finale (heen en terug)
    $cup->rounds()->create([
        'round_name' => '1/4 Finale Heenwedstrijd',
        'start_date' => now()->addWeeks(2),
        'end_date' => now()->addWeeks(3),
    ]);
    $cup->rounds()->create([
        'round_name' => '1/4 Finale Terugwedstrijd',
        'start_date' => now()->addWeeks(3),
        'end_date' => now()->addWeeks(4),
    ]);

    // Creëer de halve finale (één wedstrijd)
    $cup->rounds()->create([
        'round_name' => 'Halve Finale',
        'start_date' => now()->addWeeks(4),
        'end_date' => now()->addWeeks(5),
    ]);

    // Creëer de finale (één wedstrijd)
    $cup->rounds()->create([
        'round_name' => 'Finale',
        'start_date' => now()->addWeeks(5),
        'end_date' => now()->addWeeks(6),
    ]);

    Log::info('Cup rounds successfully created', ['cup_id' => $cup->id]);
}

public function show(Cup $cup)
{
    $roundWinners = [];

    foreach ($cup->rounds as $round) {
        $roundWinners[$round->id] = [];

        foreach ($round->games as $game) {
            // Controleer of dit een terugwedstrijd is
            if (str_contains($game->round->round_name, 'Terugwedstrijd')) {
                $firstLegId = $game->id - 1;
                $firstLeg = CupGame::find($firstLegId);

                if ($firstLeg) {
                    // Bereken de totale score van beide wedstrijden
                    $totalHomeScore = $firstLeg->home_score + $game->home_score;
                    $totalAwayScore = $firstLeg->away_score + $game->away_score;

                    // Bepaal de winnaar
                    if ($totalHomeScore > $totalAwayScore) {
                        $winner = $game->homeTeam->name;
                    } elseif ($totalAwayScore > $totalHomeScore) {
                        $winner = $game->awayTeam->name;
                    } else {
                        $winner = 'Gelijkspel';
                    }

                    $roundWinners[$round->id][] = [
                        'home_team' => $game->homeTeam->name,
                        'away_team' => $game->awayTeam->name,
                        'total_home_score' => $totalHomeScore,
                        'total_away_score' => $totalAwayScore,
                        'winner' => $winner
                    ];
                }
            }
        }
    }

    return view('cups.show', compact('cup', 'roundWinners'));
}


public function archive()
{
    // Haal alle cups op, inclusief de seizoenen en divisies
    $cups = Cup::with(['season', 'division', 'games.homeTeam', 'games.awayTeam', 'games.round'])
        ->orderBy('season_id', 'desc')
        ->get();

    // Groepeer cups per seizoen
    $cupsGroupedBySeason = $cups->groupBy(function ($cup) {
        return $cup->season->name ?? 'Onbekend Seizoen'; // Gebruik seizoennaam als sleutel
    });

    // Controleer of er cups zijn
    if ($cupsGroupedBySeason->isEmpty()) {
        return view('cups.archive', ['message' => 'Geen oude bekers beschikbaar.']);
    }

    return view('cups.archive', compact('cupsGroupedBySeason'));
}


    // Belangrijke methode: Haal teams op basis van divisie en seizoen op
    public function getTeamsByDivisionAndSeason($divisionId, $seasonId)
{
    Log::info('Start ophalen van teams', ['division_id' => $divisionId, 'season_id' => $seasonId]);

    $division = Division::find($divisionId);
    $season = Season::find($seasonId);

    if (!$division || !$season) {
        Log::error('Division of Season niet gevonden', ['division_id' => $divisionId, 'season_id' => $seasonId]);
        return response()->json(['message' => 'Division of Season niet gevonden'], 404);
    }

    Log::info('Division en Season gevonden', ['division' => $division->name, 'season' => $season->name]);

    try {
        $teams = Team::whereHas('divisions', function ($query) use ($divisionId) {
            $query->where('division_id', $divisionId);
        })->whereHas('teamSeasonStats', function ($query) use ($seasonId) {
            $query->where('season_id', $seasonId);
        })->get();

        Log::info('Teams opgehaald', ['teams_count' => $teams->count()]);

        if ($teams->isEmpty()) {
            Log::warning('Geen teams gevonden voor divisie en seizoen.');
            return response()->json(['message' => 'Geen teams gevonden voor de geselecteerde divisie en seizoen.'], 404);
        }

        return response()->json($teams);
    } catch (\Exception $e) {
        Log::error('Fout bij het ophalen van teams', ['error' => $e->getMessage()]);
        return response()->json(['message' => 'Fout bij het ophalen van teams.'], 500);
    }
}

    public function selectGames(Cup $cup)
    {
        Log::info('Geselecteerde divisie en seizoen', ['division_id' => $cup->division_id, 'season_id' => $cup->season_id]);

        // Haal divisie en seizoen op
        $selectedDivisionId = $cup->division_id;
        $selectedSeasonId = $cup->season_id;

        // Check of divisie en seizoen bestaan
        if (!$selectedDivisionId || !$selectedSeasonId) {
            Log::error('Geen juiste divisie of seizoen gevonden.');
            return redirect()->back()->with('error', 'Geen juiste divisie of seizoen gevonden.');
        }

        $divisions = Division::all();
        $seasons = Season::all();

        Log::info('Start ophalen van teams voor geselecteerde divisie en seizoen');

        // Probeer teams op te halen die behoren tot de divisie en seizoen
        try {
            $teams = Team::whereHas('divisions', function ($query) use ($selectedDivisionId) {
                $query->where('division_id', $selectedDivisionId);
            })->whereHas('teamSeasonStats', function ($query) use ($selectedSeasonId) {
                $query->where('season_id', $selectedSeasonId);
            })->get();

            Log::info('Aantal gevonden teams:', ['count' => $teams->count()]);

            if ($teams->isEmpty()) {
                Log::warning('Geen teams gevonden voor divisie en seizoen.');
                return redirect()->back()->with('error', 'Geen teams gevonden voor de geselecteerde divisie en seizoen.');
            }

            $combinations = [];
            foreach ($teams as $i => $homeTeam) {
                for ($j = $i + 1; $j < count($teams); $j++) {
                    $awayTeam = $teams[$j];
                    $combinations[] = [
                        'home_team' => $homeTeam,
                        'away_team' => $awayTeam,
                    ];
                }
            }

            Log::info('Aantal mogelijke wedstrijden:', ['count' => count($combinations)]);

            return view('cups.select_games', compact('cup', 'combinations', 'divisions', 'seasons', 'selectedDivisionId', 'selectedSeasonId', 'teams'));
        } catch (\Exception $e) {
            Log::error('Fout bij het ophalen van teams voor divisie en seizoen', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Er is een fout opgetreden bij het ophalen van teams.');
        }
    }

    public function storeSelectedGames(Request $request, Cup $cup)
    {
        $validated = $request->validate([
            'games' => 'required|array',
            'games.*' => 'string',
        ]);

        foreach ($validated['games'] as $game) {
            list($homeTeamId, $awayTeamId) = explode(',', $game);

            // Controleer of deze wedstrijd al bestaat in de CupGames
            if (CupGame::where('cup_id', $cup->id)->where('home_team_id', $homeTeamId)->where('away_team_id', $awayTeamId)->exists()) {
                Log::warning("Wedstrijd tussen $homeTeamId en $awayTeamId bestaat al.");
                continue;
            }

            CupGame::create([
                'cup_id' => $cup->id,
                'home_team_id' => $homeTeamId,
                'away_team_id' => $awayTeamId,
                'date' => now(),
            ]);
        }

        return redirect()->route('cups.show', $cup->id)->with('success', 'Wedstrijden succesvol toegevoegd aan de beker.');
    }

    // 5. Add a new Game to a Cup
    public function addGame(Cup $cup)
    {
        Log::info('addGame method reached', ['cup_id' => $cup->id]);
    
        // Haal alle teams van deze beker op voor de selectielijst
        $teams = $cup->teams;
    
        Log::info('Teams opgehaald voor Cup', ['teams_count' => $teams->count()]);
    
        // Check if teams are properly loaded
        if ($teams->isEmpty()) {
            Log::warning('Geen teams gevonden voor de geselecteerde beker', ['cup_id' => $cup->id]);
            return redirect()->back()->with('error', 'Geen teams gevonden voor de geselecteerde beker.');
        }
    
        return view('cups.add_game', compact('cup', 'teams'));
    }
    

    public function storeGame(Request $request, Cup $cup)
{
    Log::info('Start storing a new game for the cup', [
        'cup_id' => $cup->id,
        'request_data' => $request->all(),
    ]);

    try {
        // Controleer of het een "vrij" (bye) team betreft
        $isBye = $request->has('bye_team_id') && !empty($request->input('bye_team_id'));
        Log::info('Is this a bye game?', ['is_bye' => $isBye]);

        // Pas validatie aan afhankelijk van of het een "vrij" team is
        $validated = $request->validate([
            'round_id' => 'required|exists:cup_rounds,id',
            'home_team_id' => $isBye ? 'nullable' : 'required|exists:teams,id',
            'away_team_id' => $isBye ? 'nullable' : 'required|exists:teams,id|different:home_team_id',
            'bye_team_id' => 'nullable|exists:teams,id',
            'date' => $isBye ? 'nullable' : 'required|date',
            'return_date' => $isBye ? 'nullable' : 'nullable|date|after_or_equal:date',
        ]);

        Log::info('Validation passed', ['validated_data' => $validated]);

        if ($isBye) {
            // Als het een "vrij" team is, sla zowel heen- als terugwedstrijd op met de "vrij" status
            $game = CupGame::create([
                'cup_id' => $cup->id,
                'cup_round_id' => $validated['round_id'],
                'home_team_id' => $validated['bye_team_id'],
                'away_team_id' => null,
                'date' => null,
            ]);

            Log::info('Bye game created for first leg', ['game_id' => $game->id]);

            // Zoek de corresponderende terugronde en maak ook daar de wedstrijd aan
            $heenRound = CupRound::find($validated['round_id']);
            $roundPhase = preg_replace('/\sHeenwedstrijd/', '', $heenRound->round_name);
            $returnRound = CupRound::where('cup_id', $cup->id)
                ->where('round_name', 'LIKE', '%' . $roundPhase . '%Terugwedstrijd')
                ->first();

            if ($returnRound) {
                $returnGame = CupGame::create([
                    'cup_id' => $cup->id,
                    'cup_round_id' => $returnRound->id,
                    'home_team_id' => $validated['bye_team_id'],
                    'away_team_id' => null,
                    'date' => null,
                ]);

                Log::info('Bye game created for second leg', ['game_id' => $returnGame->id]);
            } else {
                Log::warning('Return round not found', ['round_phase' => $roundPhase]);
            }
        } else {
            // Normale wedstrijd aanmaken
            $game = CupGame::create([
                'cup_id' => $cup->id,
                'cup_round_id' => $validated['round_id'],
                'home_team_id' => $validated['home_team_id'],
                'away_team_id' => $validated['away_team_id'],
                'date' => $validated['date'],
            ]);

            Log::info('Normal game created', ['game_id' => $game->id]);

            // Maak de terugwedstrijd aan als er een terugdatum is ingevoerd
            if (!empty($validated['return_date'])) {
                $heenRound = CupRound::find($validated['round_id']);
                $roundPhase = preg_replace('/\sHeenwedstrijd/', '', $heenRound->round_name);

                $returnRound = CupRound::where('cup_id', $cup->id)
                    ->where('round_name', 'LIKE', '%' . $roundPhase . '%Terugwedstrijd')
                    ->first();

                if ($returnRound) {
                    $returnGame = CupGame::create([
                        'cup_id' => $cup->id,
                        'cup_round_id' => $returnRound->id,
                        'home_team_id' => $validated['away_team_id'],
                        'away_team_id' => $validated['home_team_id'],
                        'date' => $validated['return_date'],
                    ]);

                    Log::info('Return game created', ['game_id' => $returnGame->id]);
                } else {
                    Log::warning('Return round not found for normal game', ['round_phase' => $roundPhase]);
                }
            }
        }

        return response()->json(['success' => true]);

    } catch (ValidationException $e) {
        Log::error('Validation failed', ['errors' => $e->errors()]);
        return response()->json(['success' => false, 'message' => 'Validatiefout', 'errors' => $e->errors()], 422);
    } catch (\Exception $e) {
        Log::error('Error storing game', ['error' => $e->getMessage()]);
        return response()->json(['success' => false, 'message' => 'Server error', 'error' => $e->getMessage()], 500);
    }
}




// Functie om een wedstrijd te bewerken
public function editGame(Cup $cup, CupGame $game)
{
    // Haal alle rondes op voor de bewerkingsopties
    $rounds = $cup->rounds;

    // Haal de teams op door gebruik te maken van de relatie met divisies en seizoenen
    $teams = Team::whereHas('divisions', function ($query) use ($cup) {
        $query->where('division_id', $cup->division_id);
    })->whereHas('teamSeasonStats', function ($query) use ($cup) {
        $query->where('season_id', $cup->season_id);
    })->get();

    return view('cups.edit_game', compact('cup', 'game', 'rounds', 'teams'));
}



// Functie om een wedstrijd bij te werken
public function updateGame(Request $request, Cup $cup, CupGame $game)
{
    // Valideer de gegevens
    $validated = $request->validate([
        'round_id' => 'required|exists:cup_rounds,id',
        'home_team_id' => 'required|exists:teams,id',
        'away_team_id' => 'required|exists:teams,id|different:home_team_id',
        'date' => 'required|date',
    ]);

    // Update de wedstrijd
    $game->update([
        'cup_round_id' => $validated['round_id'],
        'home_team_id' => $validated['home_team_id'],
        'away_team_id' => $validated['away_team_id'],
        'date' => $validated['date'],
    ]);

    return redirect()->route('cups.show', $cup->id)->with('success', 'Wedstrijd succesvol bijgewerkt.');
}


// Functie om een wedstrijd te verwijderen
public function destroyGame(Cup $cup, CupGame $game)
{
    $currentGameId = $game->id;

    // Zoek corresponderende wedstrijd op basis van het ID en teams
    $correspondingGameNext = CupGame::where('id', $currentGameId + 1)
        ->where('home_team_id', $game->away_team_id)
        ->where('away_team_id', $game->home_team_id)
        ->first();

    $correspondingGamePrevious = CupGame::where('id', $currentGameId - 1)
        ->where('home_team_id', $game->away_team_id)
        ->where('away_team_id', $game->home_team_id)
        ->first();

    // Verwijder corresponderende wedstrijd indien gevonden
    if ($correspondingGameNext) {
        $correspondingGameNext->delete();
        Log::info('Corresponderende wedstrijd (volgende ID) succesvol verwijderd', ['game_id' => $correspondingGameNext->id]);
    } elseif ($correspondingGamePrevious) {
        $correspondingGamePrevious->delete();
        Log::info('Corresponderende wedstrijd (vorige ID) succesvol verwijderd', ['game_id' => $correspondingGamePrevious->id]);
    } else {
        Log::warning('Geen corresponderende wedstrijd gevonden voor wedstrijd ID: ' . $currentGameId);
    }

    // Verwijder de huidige wedstrijd
    $game->delete();
    Log::info('Wedstrijd succesvol verwijderd', ['game_id' => $currentGameId]);

    return redirect()->route('cups.show', $cup->id)->with('success', 'Wedstrijd(en) succesvol verwijderd.');
}







    // 7. Edit a Cup
    public function edit(Cup $cup)
    {
        // Haal alle seizoenen en divisies op om te wijzigen
        $seasons = Season::all();
        $divisions = Division::all();
        return view('cups.edit', compact('cup', 'seasons', 'divisions'));
    }

    // 8. Update a Cup
    public function update(Request $request, Cup $cup)
    {
        // Valideer het verzoek
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'season_id' => 'required|exists:seasons,id',
            'division_id' => 'required|exists:divisions,id',
        ]);

        // Update de beker
        $cup->update([
            'name' => $validated['name'],
            'season_id' => $validated['season_id'],
            'division_id' => $validated['division_id'],
        ]);

        return redirect()->route('cups.index')->with('success', 'Beker succesvol bijgewerkt.');
    }

    public function destroy(Cup $cup)
{
    // Verwijder eerst alle gerelateerde cup rounds en cup games
    $cup->rounds()->delete(); // Verwijdert alle rondes van deze beker
    $cup->games()->delete();  // Verwijdert alle wedstrijden van deze beker

    // Verwijder de beker zelf
    $cup->delete();

    return redirect()->route('cups.index')->with('success', 'Beker succesvol verwijderd.');
}
}
