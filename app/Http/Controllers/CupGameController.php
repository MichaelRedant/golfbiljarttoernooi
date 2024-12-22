<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Cup;
use App\Models\Game;
use App\Models\Team;
use App\Models\Manche;
use App\Models\Player;
use App\Models\Season;
use App\Models\CupGame;
use App\Models\LiveScore;
use App\Models\MancheCup;
use App\Models\LiveScoreCup;
use Illuminate\Http\Request;
use App\Services\GameService;
use App\Services\LiveScoreService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CupGameController extends Controller
{
    protected $gameService;
    protected $gameController;
    protected $liveScoreService;

    public function __construct(GameService $gameService , GameController $gameController , LiveScoreService $liveScoreService)
    {
        $this->gameService = $gameService;
        $this->gameController = $gameController;
        $this->liveScoreService = $liveScoreService;
    }

    // Functie om te bepalen of een testmatch vereist is
    protected function requiresTestMatch(CupGame $game)
{
    Log::info('Checking if test match is required', ['game_id' => $game->id]);

    if (!$game->round) {
        Log::error('Geen ronde gekoppeld aan deze wedstrijd', ['game_id' => $game->id]);
        return false;
    }

    // Halve finale en finale: enkelvoudige wedstrijden
    $isFinal = in_array($game->round->round_name, ['Halve Finale', 'Finale']);
    $isKnockoutRound = in_array($game->round->round_name, ['1/8 Finale Terugwedstrijd', '1/4 Finale Terugwedstrijd']);

    if ($isFinal || $isKnockoutRound) {
        // Voor knockout ronde en finale, we controleren of de huidige wedstrijd en totale score gelijk zijn
        return $this->isAggregateDraw($game);
    }

    return false;
}


protected function isAggregateDraw(CupGame $game)
{
    $firstLeg = CupGame::where('cup_id', $game->cup_id)
        ->where('cup_round_id', $game->cup_round_id - 1) 
        ->where('home_team_id', $game->home_team_id)
        ->where('away_team_id', $game->away_team_id)
        ->first();

    if ($firstLeg) {
        $totalHomeScore = $firstLeg->home_score + $game->home_score;
        $totalAwayScore = $firstLeg->away_score + $game->away_score;

        return $totalHomeScore === $totalAwayScore;
    }

    return false;
}



public function startGame(Cup $cup, CupGame $game)
{
    $user = auth()->user();
    $isAdmin = $user && $user->role === 'admin';

    Log::info('startGame method hit', ['cup_id' => $cup->id, 'game_id' => $game->id, 'user_id' => $user->id, 'is_admin' => $isAdmin]);

    if ($this->gameService->canStartGame($game, $isAdmin)) 
    {
        Log::info('Starting cup game', ['game_id' => $game->id, 'user_id' => $user->id]);
        
        // Controleer of de wedstrijd daadwerkelijk bestaat in de games-tabel
        if (!$game->exists) {
            Log::error('Game does not exist in the database', ['game_id' => $game->id]);
            return redirect()->back()->withErrors('De geselecteerde wedstrijd bestaat niet in het systeem.');
        }

        // Update de status van de game
        $game->update(['status' => 'started']);
        
        // Check of LiveScore al bestaat
        if (!$game->liveScore) {
            $initialScores = [];
            for ($i = 0; $i < 6; $i++) { // Assuming max 6 manches
                $initialScores[] = [
                    'home_player_name' => 'Nog niet gestart',
                    'away_player_name' => 'Nog niet gestart',
                    'home_player_team' => $game->homeTeam->name ?? 'Onbekend',
                    'away_player_team' => $game->awayTeam->name ?? 'Onbekend',
                    '1M' => null, 
                    '2M' => null, 
                    'Belle' => null, 
                    'WinnerId' => null,
                ];
            }

            try {
                // Creëer de LiveScore met transactionele veiligheid
                DB::transaction(function () use ($game, $initialScores) {
                    LiveScoreCup::create([
                        'game_id' => $game->id,
                        'data' => json_encode([
                            'home_team_name' => $game->homeTeam->name ?? 'Onbekend',
                            'away_team_name' => $game->awayTeam->name ?? 'Onbekend',
                            'home_score' => 0,
                            'away_score' => 0,
                            'scores' => $initialScores,
                            'division_name' => $game->division->name ?? '',
                            'game_date' => $game->date ? $game->date->format('Y-m-d') : 'Onbekende datum',
                        ]),
                    ]);
                });

                Log::info('Live score created for cup game ID: ' . $game->id);
            } catch (\Exception $e) {
                Log::error('Error creating live score', [
                    'game_id' => $game->id,
                    'error' => $e->getMessage(),
                ]);
                return redirect()->back()->withErrors('Er is een fout opgetreden bij het aanmaken van de live score.');
            }
        }

        Log::info('Redirecting to cup_match_form', ['cup_id' => $cup->id, 'game_id' => $game->id]);
        
        return redirect()->route('cup_match_form', ['cup' => $cup->id, 'game' => $game->id])
            ->with('success', 'Wedstrijd gestart!');
    }

    return redirect()->back()->with('error', 'Je bent niet bevoegd om deze wedstrijd te starten.');
}



public function show(Cup $cup, CupGame $game)
{
    Log::info('Entering show method for CupGame', ['game_id' => $game->id, 'cup_id' => $cup->id]);

    // Haal de benodigde seizoensinformatie op
    $currentSeasonId = Season::latest('id')->value('id');
    $seasons = Season::all();
    $season = Season::find($currentSeasonId);

    // Controleer of een actief seizoen beschikbaar is
    if (!$currentSeasonId) {
        Log::error('Geen actief seizoen gevonden.');
        return back()->withErrors('Geen actief seizoen gevonden.');
    }

    // Controleer of een bekerwedstrijd een knock-outwedstrijd is
    $isKnockoutRound = str_contains($game->round->round_name, 'Terugwedstrijd') 
        || in_array($game->round->round_name, ['1/8 Finale Terugwedstrijd', '1/4 Finale Terugwedstrijd']);

    // Bereken de totale score als het een terugwedstrijd betreft
    $totalScore = $isKnockoutRound ? $this->getTotalScore($game) : null;

    // Laad alle relaties voor de huidige wedstrijd
    $game->load([
        'homeTeam',
        'awayTeam',
        'manches.player1',
        'manches.player2',
        'round',
    ]);

    Log::info('Loaded game relations', ['game_id' => $game->id]);

    // Haal alle bekerwedstrijden op voor de huidige beker, gesorteerd op datum
    $cupGames = CupGame::with(['homeTeam', 'awayTeam', 'round'])
        ->where('cup_id', $cup->id)
        ->orderBy('date', 'asc')
        ->get()
        ->groupBy(function ($game) {
            return \Carbon\Carbon::parse($game->date)->format('d-m-Y');
        });

    // Controleer of een testwedstrijd vereist is
    $testMatchRequired = $this->requiresTestMatch($game);

    // Bereken scores voor de weergave
    $scores = [];
    $mostRecentManche = $game->manches()->orderBy('updated_at', 'desc')->first();

    // Initieer $liveData als null voor consistentie
    $liveData = null;

    if ($mostRecentManche && $mostRecentManche->updated_at > ($game->updated_at ?? now())) {
        // Gebruik manche data als deze recenter is dan de game-updates
        foreach ($game->manches as $manche) {
            $homePlayerName = optional($manche->player1)->first_name . ' ' . optional($manche->player1)->last_name;
            $awayPlayerName = optional($manche->player2)->first_name . ' ' . optional($manche->player2)->last_name;

            $homePlayerTeamName = $this->getPlayerActualTeamName($homePlayerName);
            $awayPlayerTeamName = $this->getPlayerActualTeamName($awayPlayerName);

            $scores[] = [
                'home_player_name' => $homePlayerName ?? 'Onbekend',
                'home_player_team' => $homePlayerTeamName,
                'away_player_name' => $awayPlayerName ?? 'Onbekend',
                'away_player_team' => $awayPlayerTeamName,
                '1M' => $manche->score1 ?? 'N/A',
                '2M' => $manche->score2 ?? 'N/A',
                'Belle' => $manche->belle_score ?? 'N/A',
                'Winner' => $manche->winner_id 
                    ? $this->getPlayerName($manche->winner_id, $game->manches->keyBy('id')->toArray())
                    : ($homePlayerName === 'forfeit' ? $awayPlayerName : ($awayPlayerName === 'forfeit' ? $homePlayerName : 'Onbekend')),
            ];
        }
    } else {
        // Gebruik LiveScore-gegevens als er geen recente manches zijn
        $liveScore = LiveScoreCup::where('game_id', $game->id)->first();
        $liveData = $liveScore ? json_decode($liveScore->data, true) : null;

        if ($liveData && isset($liveData['scores'])) {
            // Haal alle spelersinformatie op voor live data
            $playerIds = array_merge(
                array_column($liveData['scores'], 'home_player'),
                array_column($liveData['scores'], 'away_player')
            );
            $players = Player::whereIn('id', $playerIds)->get()->keyBy('id')->toArray();

            foreach ($liveData['scores'] as $score) {
                $homePlayerTeamName = $this->getPlayerActualTeamName($score['home_player_name']);
                $awayPlayerTeamName = $this->getPlayerActualTeamName($score['away_player_name']);

                $scores[] = [
                    'home_player_name' => $score['home_player_name'] ?? 'Onbekend',
                    'home_player_team' => $homePlayerTeamName,
                    'away_player_name' => $score['away_player_name'] ?? 'Onbekend',
                    'away_player_team' => $awayPlayerTeamName,
                    '1M' => $score['1M'] ?? 'N/A',
                    '2M' => $score['2M'] ?? 'N/A',
                    'Belle' => $score['Belle'] ?? 'N/A',
                    'Winner' => $score['WinnerId'] 
                        ? $this->getPlayerName($score['WinnerId'], $players)
                        : ($score['home_player_name'] === 'forfeit' ? $score['away_player_name'] : ($score['away_player_name'] === 'forfeit' ? $score['home_player_name'] : 'Onbekend')),
                ];
            }
        }
    }

    Log::info('Prepared scores for the view', ['scores' => $scores]);

    // Return de view met alle relevante data
    return view('cupGames.show', compact(
        'cup',
        'game',
        'cupGames',
        'seasons',
        'currentSeasonId',
        'season',
        'testMatchRequired',
        'totalScore',
        'scores',
        'liveData'
    ));
}


public function edit(CupGame $game)
{
    Log::info('CupGame edit method called with:', ['game_id' => $game->id]);

    $user = auth()->user();

    // Controleer of de gebruiker bevoegd is
    if ($user->role !== 'admin') {
        Log::warning('Unauthorized access attempt to edit a game', [
            'user_id' => $user->id,
            'game_id' => $game->id,
            'required_role' => 'admin',
            'user_role' => $user->role,
        ]);
        return redirect()->route('cups.games.show', ['cup' => $game->cup_id, 'game' => $game->id])
            ->withErrors(['msg' => 'Je bent niet bevoegd om deze bekerwedstrijd te bewerken.']);
    }

    // Laad benodigde relaties
    $game->load([
        'homeTeam.club.teams.players',
        'awayTeam.club.teams.players',
        'manches.player1',
        'manches.player2',
    ]);

    // Haal live score gegevens op
    $liveScore = LiveScoreCup::where('game_id', $game->id)->first();
    $liveData = $liveScore ? json_decode($liveScore->data, true) : ['scores' => [], 'testmatch_scores' => []];

    // Spelerinformatie ophalen
    $homeTeamPlayers = $game->homeTeam->club->teams->flatMap(fn($team) => $team->players)->keyBy('id');
    $awayTeamPlayers = $game->awayTeam->club->teams->flatMap(fn($team) => $team->players)->keyBy('id');

    Log::info('Players fetched for editing', [
        'home_team_players' => $homeTeamPlayers->keys()->toArray(),
        'away_team_players' => $awayTeamPlayers->keys()->toArray(),
    ]);

    // Verwerk spelersnamen en forfait-logica in de scores
    $scores = $liveData['scores'] ?? [];
    foreach ($scores as &$score) {
        // Valideer of de spelergegevens bestaan
        $homePlayer = $score['home_player'] ?? null;
        $awayPlayer = $score['away_player'] ?? null;

        // Verwerk spelergegevens
        $score['home_player_name'] = $homePlayer === 'forfeit'
            ? 'Forfait'
            : $this->getPlayerName($homePlayer, $homeTeamPlayers);
        $score['away_player_name'] = $awayPlayer === 'forfeit'
            ? 'Forfait'
            : $this->getPlayerName($awayPlayer, $awayTeamPlayers);

        // Pas de scorelogica toe voor forfait
        if ($homePlayer === 'forfeit') {
            $score['1M'] = '2';
            $score['2M'] = '2';
            $score['WinnerId'] = $this->getPlayerIdByName($score['away_player_name']);
        } elseif ($awayPlayer === 'forfeit') {
            $score['1M'] = '1';
            $score['2M'] = '1';
            $score['WinnerId'] = $this->getPlayerIdByName($score['home_player_name']);
        } else {
            // Als geen forfait, zorg ervoor dat bestaande waarden worden behouden
            $score['1M'] = $score['1M'] ?? 'N/A';
            $score['2M'] = $score['2M'] ?? 'N/A';
        }
    }

    // Verwerk testmatch scores
    $testmatchScores = $liveData['testmatch_scores'] ?? [];
    foreach ($testmatchScores as &$testmatchScore) {
        $testmatchScore['home_player_name'] = $this->getPlayerName($testmatchScore['home_player'] ?? null, $homeTeamPlayers);
        $testmatchScore['away_player_name'] = $this->getPlayerName($testmatchScore['away_player'] ?? null, $awayTeamPlayers);
    }

    // Totale score berekenen op basis van gespeelde manches
    $homeScore = array_reduce($scores, function ($carry, $item) {
        $carry += ($item['1M'] ?? null) == 1 ? 1 : 0;
        return $carry;
    }, 0);

    $awayScore = array_reduce($scores, function ($carry, $item) {
        $carry += ($item['1M'] ?? null) == 2 ? 1 : 0;
        return $carry;
    }, 0);

    Log::info('Calculated total scores', ['home_score' => $homeScore, 'away_score' => $awayScore]);

    return view('cupGames.edit', compact('game', 'homeTeamPlayers', 'awayTeamPlayers', 'scores', 'testmatchScores', 'homeScore', 'awayScore'));
}


public function editForm(Cup $cup, CupGame $game)
{
    Log::info('editForm method called', ['cup_id' => $cup->id, 'game_id' => $game->id]);

    // Laad de benodigde relaties voor de wedstrijd en spelers
    $game->load([
        'homeTeam' => function ($query) {
            $query->with(['club.teams.players']);
        },
        'awayTeam' => function ($query) {
            $query->with(['club.teams.players']);
        },
        'manches.player1',
        'manches.player2',
        'round',
    ]);

    Log::info('Game relations loaded successfully', ['game_id' => $game->id]);

    // Haal alle spelers van het thuisteam en teams binnen dezelfde club op
    $homeTeamPlayers = $game->homeTeam->club->teams->flatMap(fn($team) => $team->players)->unique('id');
    $awayTeamPlayers = $game->awayTeam->club->teams->flatMap(fn($team) => $team->players)->unique('id');

    // Haal of creëer de LiveScore voor de wedstrijd
    $liveScore = LiveScoreCup::firstOrCreate(
        ['game_id' => $game->id],
        ['data' => json_encode([
            'home_team_name' => $game->homeTeam->name ?? 'Onbekend',
            'away_team_name' => $game->awayTeam->name ?? 'Onbekend',
            'home_score' => $game->home_score ?? 0,
            'away_score' => $game->away_score ?? 0,
            'scores' => array_fill(0, 6, [
                'home_player_name' => 'Nog niet gestart',
                'away_player_name' => 'Nog niet gestart',
                'home_player_team' => $game->homeTeam->name ?? 'Onbekend',
                'away_player_team' => $game->awayTeam->name ?? 'Onbekend',
                '1M' => null,
                '2M' => null,
                'Belle' => null,
                'WinnerId' => null,
            ]),
            'testmatch_scores' => array_fill(0, 3, [
                'home_player_name' => 'Nog niet gestart',
                'away_player_name' => 'Nog niet gestart',
                '1M' => null,
            ]),
        ])]
    );

    Log::info('LiveScore loaded or created successfully', ['game_id' => $game->id]);

    // Decode LiveScore data
    $liveScoreData = json_decode($liveScore->data, true);

    // Valideer en standaardiseer wedstrijdscores
    $liveScoreData['scores'] = array_map(function ($score) use ($game) {
        return array_merge([
            'home_player_name' => 'Nog niet gestart',
            'away_player_name' => 'Nog niet gestart',
            'home_player_team' => $game->homeTeam->name ?? 'Onbekend',
            'away_player_team' => $game->awayTeam->name ?? 'Onbekend',
            '1M' => null,
            '2M' => null,
            'Belle' => null,
            'WinnerId' => null,
        ], $score);
    }, $liveScoreData['scores'] ?? array_fill(0, 6, []));

    // Verwerk forfait-logica
    foreach ($liveScoreData['scores'] as &$score) {
        if ($score['home_player_name'] === 'forfeit') {
            $score['WinnerId'] = $this->getPlayerIdByName($score['away_player_name']);
        } elseif ($score['away_player_name'] === 'forfeit') {
            $score['WinnerId'] = $this->getPlayerIdByName($score['home_player_name']);
        }
    }

    // Valideer en standaardiseer `TestMatch`-scores
    $liveScoreData['testmatch_scores'] = array_map(function ($testMatch) use ($game) {
        return array_merge([
            'home_player_name' => 'Nog niet gestart',
            'away_player_name' => 'Nog niet gestart',
            '1M' => null,
        ], $testMatch);
    }, $liveScoreData['testmatch_scores'] ?? array_fill(0, 3, []));

    // Werk LiveScore bij als wijzigingen zijn aangebracht
    $liveScore->data = json_encode($liveScoreData);
    $liveScore->save();

    Log::info('Scores and TestMatch scores validated and saved.', ['game_id' => $game->id]);

    // Bereid de wedstrijdscores voor de view voor
    $scores = collect($liveScoreData['scores'])->toArray();

    // Bereid de testmatchscores voor de view voor
    $testMatchScores = collect($liveScoreData['testmatch_scores'])->toArray();

    // Controleer of een testmatch vereist is
    $testMatchRequired = $this->requiresTestMatch($game);

    // Bereken de totale score voor terugwedstrijden
    $totalScore = $this->getTotalScore($game) ?? [
        'first_leg_home_score' => 0,
        'first_leg_away_score' => 0,
        'second_leg_home_score' => 0,
        'second_leg_away_score' => 0,
        'total_home_score' => 0,
        'total_away_score' => 0,
    ];

    Log::info('Prepared data for the view.', [
        'testMatchRequired' => $testMatchRequired,
        'totalScore' => $totalScore,
        'scores' => $scores,
        'testMatchScores' => $testMatchScores,
    ]);

    // Return de bewerkingspagina voor de CupGame
    return view('cups.cup_match_form', compact(
        'game',
        'homeTeamPlayers',
        'awayTeamPlayers',
        'scores',
        'testMatchRequired',
        'testMatchScores',
        'totalScore',
        'liveScoreData'
    ));
}



public function showLiveScores()
{
    Log::info('Entering showLiveScores method in CupGameController');

    // Haal live scores op via de LiveScoreService
    $liveData = $this->liveScoreService->getLiveScoresForGames();

    // Controleer of er een bericht is (bijvoorbeeld geen wedstrijden beschikbaar)
    if (isset($liveData['message'])) {
        return view('live-scores', ['message' => $liveData['message']]);
    }

    // Verwerk live scores en pas "forfeit"-logica toe
    $processedLiveData = array_map(function ($game) {
        // Controleer of er scores beschikbaar zijn
        if (isset($game['scores']) && is_array($game['scores'])) {
            foreach ($game['scores'] as &$score) {
                // Verwerk "forfeit"-logica
                if ($score['home_player'] === 'forfeit') {
                    $score['1M'] = '2';
                    $score['2M'] = '2';
                } elseif ($score['away_player'] === 'forfeit') {
                    $score['1M'] = '1';
                    $score['2M'] = '1';
                }
            }
        }

        return $game;
    }, $liveData);

    Log::info('Processed live data with forfeit logic:', ['processedLiveData' => $processedLiveData]);

    // Toon de live scores in de view
    return view('live-scores', ['liveData' => $processedLiveData]);
}



    // Functie om een bekerwedstrijd aan te maken
    public function store(Request $request)
    {
        Log::info('Store method called for CupGame');
        Log::info('Request data: ', $request->all());

        // Validatie
        $validatedData = $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'season_id' => 'required|exists:seasons,id',
            'date' => 'required|date',
            'home_team_id' => 'nullable|exists:teams,id',
            'away_team_id' => 'nullable|exists:teams,id',
            'bye_team_id' => 'nullable|exists:teams,id',
            'scores' => 'sometimes|array',
            'scores.*.home_player' => 'nullable|exists:players,id',
            'scores.*.away_player' => 'nullable|exists:players,id',
            'scores.*.1M' => 'nullable|integer',
            'scores.*.2M' => 'nullable|integer',
            'scores.*.Belle' => 'nullable|integer',
            'home_captain' => 'nullable|exists:players,id',
            'away_captain' => 'nullable|exists:players,id',
            'home_reserve' => 'nullable|exists:players,id',
            'away_reserve' => 'nullable|exists:players,id',
            'is_cup_game' => 'boolean',
            'cup_round_id' => 'required|exists:cup_rounds,id',
        ]);

        // Logica voor Bye team
        if ($validatedData['bye_team_id']) {
            $validatedData['home_team_id'] = null;
            $validatedData['away_team_id'] = null;
        } else {
            // Validatie als er geen bye team is
            $request->validate([
                'home_team_id' => 'required|exists:teams,id',
                'away_team_id' => 'required|exists:teams,id',
            ]);
        }

        // Maak een nieuwe CupGame aan
        $cupGame = CupGame::create($validatedData);
        Log::info('CupGame created: ', ['game_id' => $cupGame->id]);

        // Verwerk de scores indien beschikbaar
        if (isset($validatedData['scores'])) {
            foreach ($validatedData['scores'] as $index => $score) {
                // Bewerk elke manche en creëer deze
                $this->processManche($cupGame, $index, $score);
            }
        }

        return redirect()->route('cup.match.edit', $cupGame->id)
            ->with('success', 'Bekerwedstrijd succesvol aangemaakt!');
    }

   

protected function getPlayerActualTeamName($playerName)
{
    // Zoek de teamnaam op basis van de spelernaam
    $player = Player::whereRaw("CONCAT(first_name, ' ', last_name) = ?", [$playerName])->first();
    return $player ? $player->team->name : 'Onbekend';
}


public function update(Request $request, CupGame $game)
{
    Log::info('CupGame Update method called', ['game_id' => $game->id]);

    // Validatie
    $validatedData = $request->validate([
        'date' => 'required|date',
        'home_team_id' => 'nullable|exists:teams,id',
        'away_team_id' => 'nullable|exists:teams,id',
        'bye_team_id' => 'nullable|exists:teams,id',
        'home_score' => 'nullable|integer',
        'away_score' => 'nullable|integer',
        'manches' => 'nullable|array',
        'manches.*.player1_id' => 'nullable|exists:players,id',
        'manches.*.player2_id' => 'nullable|exists:players,id',
        'manches.*.score1' => 'nullable|integer',
        'manches.*.score2' => 'nullable|integer',
        'manches.*.belle_score' => 'nullable|integer',
    ]);

    if ($request->filled('bye_team_id')) {
        $validatedData['home_team_id'] = null;
        $validatedData['away_team_id'] = null;
    } else {
        $validatedData['bye_team_id'] = null;
    }

    // Update de bekerwedstrijd
    $game->update($validatedData);

    // Verwerk de manches
    if (isset($validatedData['manches'])) {
        foreach ($validatedData['manches'] as $index => $mancheData) {
            $this->processManche($game, $index, $mancheData);
        }
    }

    // Update LiveScore met de bijgewerkte manche gegevens
    $liveScore = LiveScoreCup::where('game_id', $game->id)->first();
    if ($liveScore) {
        $liveScoreData = json_decode($liveScore->data, true);
        $liveScoreData['home_score'] = $validatedData['home_score'] ?? $liveScoreData['home_score'];
        $liveScoreData['away_score'] = $validatedData['away_score'] ?? $liveScoreData['away_score'];
        $liveScoreData['scores'] = $game->manches->map(function ($manche) {
            return [
                'home_player_name' => $manche->player1 ? $manche->player1->first_name . ' ' . $manche->player1->last_name : 'Nog niet gestart',
                'away_player_name' => $manche->player2 ? $manche->player2->first_name . ' ' . $manche->player2->last_name : 'Nog niet gestart',
                '1M' => $manche->score1 ?? '',
                '2M' => $manche->score2 ?? '',
                'Belle' => $manche->belle_score ?? '',
                'WinnerId' => $manche->winner_id,
            ];
        })->toArray();
        $liveScore->data = json_encode($liveScoreData);
        $liveScore->save();
    }

    return redirect()->route('cups.games.show', ['cup' => $game->cup_id, 'game' => $game->id])
        ->with('success', 'Bekerwedstrijd succesvol bijgewerkt!');
}

protected function processManche(CupGame $game, int $index, array $scoreData)
{
    Log::info('Processing manche for game', ['game_id' => $game->id, 'manche_number' => $index + 1]);

    // Spelerinformatie ophalen
    $homePlayerId = $scoreData['home_player'] === 'forfeit' ? null : $scoreData['home_player'];
    $awayPlayerId = $scoreData['away_player'] === 'forfeit' ? null : $scoreData['away_player'];

    // Controleer of beide spelers ontbreken
    if ($homePlayerId === null && $awayPlayerId === null) {
        Log::warning('Both players forfeited for manche.', [
            'game_id' => $game->id,
            'manche_number' => $index + 1
        ]);
        return; // Skip processing, no valid players
    }

    // Scorevalidatie en conversie
    $score1 = $homePlayerId === null || $awayPlayerId === null ? 0 : ($scoreData['1M'] === 'N/A' ? null : (int)$scoreData['1M']);
    $score2 = $homePlayerId === null || $awayPlayerId === null ? 0 : ($scoreData['2M'] === 'N/A' ? null : (int)$scoreData['2M']);
    $belleScore = $homePlayerId === null || $awayPlayerId === null ? null : ($scoreData['Belle'] === 'N/A' ? null : (int)$scoreData['Belle']);

    // Belle score alleen verwerken als beide scores gelijk zijn en geen forfait
    if ($score1 !== null && $score2 !== null && $score1 === $score2 && $homePlayerId !== null && $awayPlayerId !== null) {
        $belleScore = ($scoreData['Belle'] !== 'N/A' && is_numeric($scoreData['Belle'])) ? (int)$scoreData['Belle'] : null;
    }

    // Winnaar bepalen
    $winnerId = null;
    if ($homePlayerId === null) {
        $winnerId = $awayPlayerId; // Forfait door home_player
        Log::info('Home player forfeited, setting away player as winner.', [
            'manche_number' => $index + 1,
            'winner_id' => $winnerId
        ]);
    } elseif ($awayPlayerId === null) {
        $winnerId = $homePlayerId; // Forfait door away_player
        Log::info('Away player forfeited, setting home player as winner.', [
            'manche_number' => $index + 1,
            'winner_id' => $winnerId
        ]);
    } else {
        $winnerId = $this->determineWinner($scoreData, $homePlayerId, $awayPlayerId);
    }

    // Log manche details voor debugging
    Log::info('Final manche details:', [
        'game_id' => $game->id,
        'manche_number' => $index + 1,
        'home_player_id' => $homePlayerId,
        'away_player_id' => $awayPlayerId,
        'score1' => $score1,
        'score2' => $score2,
        'belle_score' => $belleScore,
        'winner_id' => $winnerId,
    ]);

    try {
        // Update of creëer de manche
        MancheCup::updateOrCreate(
            [
                'game_id' => $game->id,
                'number' => $index + 1,
            ],
            [
                'player1_id' => $homePlayerId,
                'player2_id' => $awayPlayerId,
                'score1' => $score1,
                'score2' => $score2,
                'belle_score' => $belleScore,
                'winner_id' => $winnerId,
            ]
        );

        Log::info('Manche successfully processed and saved.', [
            'game_id' => $game->id,
            'manche_number' => $index + 1,
        ]);

    } catch (\Exception $e) {
        Log::error('Failed to process manche for game.', [
            'game_id' => $game->id,
            'manche_number' => $index + 1,
            'error' => $e->getMessage(),
        ]);
    }
}

    

    // Hulpmethode om de winnaar van een manche te bepalen
    private function determineWinner(array $score, $homePlayerId, $awayPlayerId)
{
    Log::info('Determining winner for manche', [
        'home_player_id' => $homePlayerId,
        'away_player_id' => $awayPlayerId,
        'scores' => $score
    ]);

    // Forfait situatie: Home geeft forfait
    if ($homePlayerId === null) {
        Log::info('Home player forfeited. Away player wins by default.', [
            'winner_id' => $awayPlayerId
        ]);
        return $awayPlayerId;
    }

    // Forfait situatie: Away geeft forfait
    if ($awayPlayerId === null) {
        Log::info('Away player forfeited. Home player wins by default.', [
            'winner_id' => $homePlayerId
        ]);
        return $homePlayerId;
    }

    // Validatie van scores en fallback naar 0 als waarden ontbreken
    $score1M = is_numeric($score['1M']) ? (int)$score['1M'] : 0;
    $score2M = is_numeric($score['2M']) ? (int)$score['2M'] : 0;
    $belleScore = is_numeric($score['Belle']) ? (int)$score['Belle'] : 0;

    // Bepalen van de winnaar op basis van manche scores
    if ($score1M > $score2M) {
        Log::info('Home player wins based on 1M and 2M scores.', [
            'winner_id' => $homePlayerId
        ]);
        return $homePlayerId;
    } elseif ($score1M < $score2M) {
        Log::info('Away player wins based on 1M and 2M scores.', [
            'winner_id' => $awayPlayerId
        ]);
        return $awayPlayerId;
    }

    // Gelijke stand in 1M en 2M: Belle score bepaalt de winnaar
    if ($belleScore > 0) {
        Log::info('Belle score breaks the tie.', [
            'winner_id' => $belleScore === 1 ? $homePlayerId : $awayPlayerId
        ]);
        return $belleScore === 1 ? $homePlayerId : $awayPlayerId;
    }

    // Geen duidelijke winnaar: Log een waarschuwing
    Log::warning('No winner could be determined based on the scores. Returning null.', [
        'home_player_id' => $homePlayerId,
        'away_player_id' => $awayPlayerId,
        'scores' => $score
    ]);

    return null; // Geen winnaar bepaald
}

    
    // Functie voor het ophalen van live scores voor een wedstrijd
    public function fetchLiveScore(CupGame $game)
{
    $liveScore = LiveScoreCup::where('game_id', $game->id)->first();

    $defaultScores = array_map(function () use ($game) {
        return [
            'home_player_name' => 'Nog niet gestart',
            'away_player_name' => 'Nog niet gestart',
            'home_player_team' => $game->homeTeam->name ?? 'Onbekend',
            'away_player_team' => $game->awayTeam->name ?? 'Onbekend',
            '1M' => '',
            '2M' => '',
            'Belle' => '',
            'WinnerId' => null,
        ];
    }, range(0, 5));

    if ($liveScore) {
        $data = json_decode($liveScore->data, true);
        $data['scores'] = $data['scores'] ?? $defaultScores;

        return response()->json($data);
    } else {
        return response()->json([
            'home_team_name' => $game->homeTeam->name ?? 'Onbekend',
            'away_team_name' => $game->awayTeam->name ?? 'Onbekend',
            'home_score' => $game->home_score ?? 0,
            'away_score' => $game->away_score ?? 0,
            'scores' => $defaultScores,
        ]);
    }
}


protected function getPlayerIdByName($name)
{
    $parts = explode(' ', $name);
    if (count($parts) < 2) {
        return null;  // Return null if the name cannot be split properly
    }

    $firstName = $parts[0];
    $lastName = implode(' ', array_slice($parts, 1));

    $player = Player::where('first_name', $firstName)
                    ->where('last_name', $lastName)
                    ->first();

    return $player ? $player->id : null;
}

protected function getPlayerName($playerId, $players)
{
    return isset($players[$playerId])
        ? $players[$playerId]['first_name'] . ' ' . $players[$playerId]['last_name']
        : '';
}

public function updateLiveScore(Request $request, CupGame $game)
{
    Log::info('Update Live Score initiated for cup game:', ['game_id' => $game->id ?? null]);

    try {
        // Controleer of de gebruiker geauthenticeerd is
        $user = auth()->user();
        if (!$user) {
            Log::warning('Unauthorized attempt to update live score.');
            return response()->json(['error' => 'Unauthorized access.'], 403);
        }

        $gameService = new GameService();

        // Controleer of de game kan worden gestart
        if (!$game || !$game->id) {
            Log::warning('Invalid game object or missing properties', ['game' => $game]);
            return response()->json(['error' => 'Invalid game provided.'], 400);
        }

        if (!$gameService->canStartGame($game, $user->role === 'admin')) {
            return response()->json(['error' => 'Live score updates are not allowed outside of the specified time frame.'], 403);
        }

        // Log ontvangen request data
        Log::info('Raw request data received:', $request->all());

        // Validatie van inkomende data
        $validatedData = $request->validate([
            'game_id' => 'required|exists:cup_games,id',
            'home_score' => 'required|integer|min:0',
            'away_score' => 'required|integer|min:0',
            'home_captain' => 'nullable|integer|exists:players,id',
            'away_captain' => 'nullable|integer|exists:players,id',
            'home_reserve' => 'nullable|integer|exists:players,id',
            'away_reserve' => 'nullable|integer|exists:players,id',
            'scores' => 'required|array',
            'scores.*.home_player' => ['nullable', 'string', function ($attribute, $value, $fail) {
                if ($value !== 'forfeit' && !ctype_digit($value)) {
                    $fail("The $attribute must be a valid player ID or 'forfeit'.");
                }
            }],
            'scores.*.away_player' => ['nullable', 'string', function ($attribute, $value, $fail) {
                if ($value !== 'forfeit' && !ctype_digit($value)) {
                    $fail("The $attribute must be a valid player ID or 'forfeit'.");
                }
            }],
            'scores.*.1M' => 'nullable|string|in:1,2,N/A',
            'scores.*.2M' => 'nullable|string|in:1,2,N/A',
            'scores.*.Belle' => 'nullable|string|in:1,2,N/A',
            'testmatch' => 'nullable|array',
            'testmatch.*.home_player' => 'nullable|integer|exists:players,id',
            'testmatch.*.away_player' => 'nullable|integer|exists:players,id',
            'testmatch.*.1M' => 'nullable|string|in:1,2,N/A',
        ]);

        Log::info('Validated data after validation:', ['validated_data' => $validatedData]);

        // Verzamelen van spelers-ID's
        $playerIds = array_map('intval', array_filter(array_merge(
            [$validatedData['home_captain'], $validatedData['away_captain'], $validatedData['home_reserve'], $validatedData['away_reserve']],
            array_column($validatedData['scores'] ?? [], 'home_player'),
            array_column($validatedData['scores'] ?? [], 'away_player'),
            array_column($validatedData['testmatch'] ?? [], 'home_player'),
            array_column($validatedData['testmatch'] ?? [], 'away_player')
        )));

        Log::info('Player IDs gathered from request:', ['player_ids' => $playerIds]);

        // Haal spelersinformatie op
        $players = Player::with('team')->whereIn('id', $playerIds)->get()->keyBy('id')->toArray();
        Log::info('Players data fetched from database:', $players);

        // Verwerk captains en reserves
        $homeCaptainName = isset($players[$validatedData['home_captain']])
            ? $players[$validatedData['home_captain']]['first_name'] . ' ' . $players[$validatedData['home_captain']]['last_name']
            : '';
        $awayCaptainName = isset($players[$validatedData['away_captain']])
            ? $players[$validatedData['away_captain']]['first_name'] . ' ' . $players[$validatedData['away_captain']]['last_name']
            : '';
        $homeReserveName = isset($players[$validatedData['home_reserve']])
            ? $players[$validatedData['home_reserve']]['first_name'] . ' ' . $players[$validatedData['home_reserve']]['last_name']
            : '';
        $awayReserveName = isset($players[$validatedData['away_reserve']])
            ? $players[$validatedData['away_reserve']]['first_name'] . ' ' . $players[$validatedData['away_reserve']]['last_name']
            : '';

        // Begin met opgegeven score
        $homeScore = (int) $validatedData['home_score'];
        $awayScore = (int) $validatedData['away_score'];

        // Verwerk reguliere scores
        $scores = [];
        foreach ($validatedData['scores'] ?? [] as $index => $score) {
            $homePlayerId = $score['home_player'] === 'forfeit' ? null : $score['home_player'];
            $awayPlayerId = $score['away_player'] === 'forfeit' ? null : $score['away_player'];
        
            $score1 = $homePlayerId === null || $awayPlayerId === null ? 0 : ($score['1M'] === 'N/A' ? null : (int)$score['1M']);
            $score2 = $homePlayerId === null || $awayPlayerId === null ? 0 : ($score['2M'] === 'N/A' ? null : (int)$score['2M']);
            $belleScore = $homePlayerId === null || $awayPlayerId === null ? null : ($score['Belle'] === 'N/A' ? null : (int)$score['Belle']);
        
            // Bepaal winnaar
            $winnerId = null;
            if ($homePlayerId === null) {
                $winnerId = $awayPlayerId; // Forfait door home_player
            } elseif ($awayPlayerId === null) {
                $winnerId = $homePlayerId; // Forfait door away_player
            } else {
                $winnerId = $this->determineWinner($score, $homePlayerId, $awayPlayerId);
            }
        
            // Opslaan van manche
            MancheCup::updateOrCreate(
                ['game_id' => $game->id, 'number' => $index + 1],
                [
                    'player1_id' => $homePlayerId,
                    'player2_id' => $awayPlayerId,
                    'score1' => $score1,
                    'score2' => $score2,
                    'belle_score' => $belleScore,
                    'winner_id' => $winnerId,
                ]
            );

            $scores[] = [
                'home_player_name' => $homePlayerId ? $players[$homePlayerId]['first_name'] . ' ' . $players[$homePlayerId]['last_name'] : 'Forfait',
                'away_player_name' => $awayPlayerId ? $players[$awayPlayerId]['first_name'] . ' ' . $players[$awayPlayerId]['last_name'] : 'Forfait',
                '1M' => $score1,
                '2M' => $score2,
                'Belle' => $belleScore,
                'WinnerId' => $winnerId,
            ];
        }

        // Gegevens om op te slaan
        $dataToStore = [
            'home_team_name' => $game->homeTeam->name ?? 'Nog niet gestart',
            'away_team_name' => $game->awayTeam->name ?? 'Nog niet gestart',
            'home_score' => $homeScore,
            'away_score' => $awayScore,
            'home_captain_name' => $homeCaptainName,
            'away_captain_name' => $awayCaptainName,
            'home_reserve_name' => $homeReserveName,
            'away_reserve_name' => $awayReserveName,
            'scores' => $scores,
        ];

        Log::info('Final data to be stored in LiveScore:', $dataToStore);

        // Update of maak de live score aan
        LiveScoreCup::updateOrCreate(
            ['game_id' => $game->id],
            ['data' => json_encode($dataToStore)]
        );

        // Update de wedstrijdscores in de CupGame-tabel
        $game->update([
            'home_score' => $homeScore,
            'away_score' => $awayScore,
        ]);

        Log::info('Live score and manches updated successfully for game.', ['game_id' => $game->id]);

        return response()->json(['success' => true], 200);
    } catch (\Exception $e) {
        Log::error('Error updating live score:', ['error' => $e->getMessage()]);
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

    
public function requestApproval($cup_id, $game_id)
{
    Log::info('requestApproval called', ['cup_id' => $cup_id, 'game_id' => $game_id]);

    // Fetch the game with its related teams, clubs, and live score
    $game = CupGame::with(['homeTeam', 'awayTeam', 'homeTeam.club', 'awayTeam.club', 'liveScoreCup'])
        ->where('id', $game_id)
        ->first();

    // Check if the game was found
    if (!$game) {
        Log::error('Game not found', ['cup_id' => $cup_id, 'game_id' => $game_id]);
        return redirect()->route('cups.show', $cup_id)->with('error', 'Wedstrijd niet gevonden');
    }

    // Check if team details are available
    if (!$game->homeTeam || !$game->awayTeam) {
        Log::error('Team details not available for the game', ['game' => $game]);
        return redirect()->route('cups.show', $cup_id)->with('error', 'Teamgegevens niet beschikbaar voor deze wedstrijd');
    }

    // Log that the game was found successfully
    Log::info('Game found', ['game' => $game]);

    // Extract live score data if available
    $liveScore = $game->liveScoreCup;
    $liveData = $liveScore ? json_decode($liveScore->data, true) : null;

    if ($liveData) {
        Log::info('Live data found for game', ['liveData' => $liveData]);
    } else {
        Log::info('No live data found for game', ['game_id' => $game_id]);
    }

    // Return the view with game and live data
    return view('cupGames.approval', compact('game', 'liveData'));
}

public function approveGame(Request $request, Cup $cup, CupGame $game)
{
    if (!$game) {
        Log::error('CupGame instance niet gevonden in approveGame');
        return redirect()->route('dashboard')->withErrors(['msg' => 'Wedstrijd niet gevonden']);
    }

    Log::info('CupGame instance gevonden', ['game_id' => $game->id]);
    Log::info('ApproveGame method called', ['game_id' => $game->id]);

    $user = auth()->user();
    Log::info('ApproveGame method called by user', ['user_id' => $user->id, 'game_id' => $game->id]);

    $isDryRun = $request->has('dry_run');
    Log::info('Dry run mode:', ['isDryRun' => $isDryRun]);

    // Controleer of de gebruiker bevoegd is
    if ($user->team_id != $game->away_team_id && $user->role !== 'admin') {
        Log::warning('Ongeautoriseerde gebruiker probeerde goed te keuren', [
            'user_id' => $user->id,
            'game_id' => $game->id,
        ]);
        return redirect()->route('dashboard')->withErrors(['msg' => 'Je bent niet bevoegd om deze wedstrijd goed te keuren.']);
    }

    Log::info('User authorized to approve game', ['user_id' => $user->id]);

    // Haal LiveScoreCup op
    $liveScore = LiveScoreCup::where('game_id', $game->id)->first();
    if (!$liveScore) {
        Log::error('Geen live score gegevens gevonden', ['game_id' => $game->id]);
        return redirect()->route('dashboard')->withErrors(['msg' => 'Geen live score gegevens gevonden voor deze wedstrijd.']);
    }

    $liveData = json_decode($liveScore->data, true);
    if (!$liveData) {
        Log::error('Fout bij het decoderen van live score data', ['game_id' => $game->id]);
        return redirect()->route('dashboard')->withErrors(['msg' => 'Ongeldige live score data.']);
    }
    Log::info('LiveScore Data:', ['live_data' => $liveData]);

    $testMatchScores = $liveData['testmatch_scores'] ?? [];
    Log::info('Testmatch data:', ['testMatchScores' => $testMatchScores]);

    DB::beginTransaction();
    try {
        // Update de game scores
        $game->home_score = $liveData['home_score'] ?? 0;
        $game->away_score = $liveData['away_score'] ?? 0;
        Log::info('Updating game scores for approval', [
            'home_score' => $game->home_score,
            'away_score' => $game->away_score,
        ]);

        if (!$isDryRun) {
            $game->away_team_approved = true;
            $game->save();
            Log::info('Wedstrijd succesvol goedgekeurd', ['game_id' => $game->id]);
        }

        // Verwerk de hoofd scores
        foreach ($liveData['scores'] ?? [] as $index => $score) {
            $homePlayerName = $score['home_player_name'] ?? 'Onbekend';
            $awayPlayerName = $score['away_player_name'] ?? 'Onbekend';

            $homePlayerId = $this->getPlayerIdByName($homePlayerName);
            $awayPlayerId = $this->getPlayerIdByName($awayPlayerName);

            $winnerId = $this->determineWinner($score, $homePlayerId, $awayPlayerId);

            Log::info('Manche verwerkt', [
                'game_id' => $game->id,
                'home_player_id' => $homePlayerId,
                'away_player_id' => $awayPlayerId,
                'score1' => $score['1M'] ?? null,
                'score2' => $score['2M'] ?? null,
                'belle_score' => $score['Belle'] ?? null,
                'winner_id' => $winnerId,
            ]);

            if (!$isDryRun) {
                MancheCup::updateOrCreate(
                    [
                        'game_id' => $game->id,
                        'number' => $index + 1,
                    ],
                    [
                        'player1_id' => $homePlayerId,
                        'player2_id' => $awayPlayerId,
                        'score1' => $score['1M'] ?? null,
                        'score2' => $score['2M'] ?? null,
                        'belle_score' => $score['Belle'] ?? null,
                        'winner_id' => $winnerId,
                    ]
                );
            }
        }

        // Verwerk de testmatch scores
        foreach ($testMatchScores as $index => $testmatch) {
            $homePlayerName = $testmatch['home_player_name'] ?? 'Onbekend';
            $awayPlayerName = $testmatch['away_player_name'] ?? 'Onbekend';

            $homePlayerId = $this->getPlayerIdByName($homePlayerName);
            $awayPlayerId = $this->getPlayerIdByName($awayPlayerName);

            Log::info('Testmatch verwerkt', [
                'game_id' => $game->id,
                'home_player_id' => $homePlayerId,
                'away_player_id' => $awayPlayerId,
                '1M' => $testmatch['1M'] ?? null,
            ]);

            if (!$isDryRun) {
                MancheCup::updateOrCreate(
                    [
                        'game_id' => $game->id,
                        'number' => 100 + $index, // Offset voor testmatches
                    ],
                    [
                        'player1_id' => $homePlayerId,
                        'player2_id' => $awayPlayerId,
                        'score1' => $testmatch['1M'] ?? null,
                        'score2' => null, // Geen tweede manche voor testmatch
                        'belle_score' => null,
                        'winner_id' => $testmatch['1M'] == 1 ? $homePlayerId : ($testmatch['1M'] == 2 ? $awayPlayerId : null),
                    ]
                );
            }
        }

        DB::commit();
        Log::info('Wedstrijd goedgekeurd', ['game_id' => $game->id]);
        return redirect()->route('home')->with('success', 'Wedstrijd succesvol goedgekeurd!');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Fout tijdens goedkeuren van wedstrijd', ['error' => $e->getMessage()]);
        return redirect()->route('dashboard')->withErrors(['msg' => $e->getMessage()]);
    }
}

 // Controleer of beide teams de wedstrijd hebben goedgekeurd
 protected function checkApprovalStatus(CupGame $game)
 {
     if ($game->home_team_approved && $game->away_team_approved) {
         Log::info('Both teams approved the game', ['game_id' => $game->id]);
         $game->update(['approved' => true]);
     } else {
         Log::info('Waiting for the other team to approve', ['game_id' => $game->id]);
     }
 }

 // Hulpmethode om goedkeuringsvlaggen bij te werken (optioneel, voor toekomstige uitbreiding)
 protected function updateApprovalFlags(CupGame $game, bool $homeApproved, bool $awayApproved)
 {
     $game->home_team_approved = $homeApproved;
     $game->away_team_approved = $awayApproved;
     $game->save();
 }

 protected function getTotalScore(CupGame $game)
 {
     Log::info('Calculating total score for game', ['game_id' => $game->id]);
 
     // Controleer of de huidige game een terugwedstrijd is
     if (str_contains($game->round->round_name, 'Terugwedstrijd')) {
         // Zoek de heenwedstrijd op basis van het huidige ID - 1
         $firstLegId = $game->id - 1;
         $firstLeg = CupGame::find($firstLegId);
 
         if (!$firstLeg) {
             Log::error('No first leg found for return game', ['return_game_id' => $game->id]);
             return null;
         }
 
         // Haal de huidige scores op uit de LiveScore voor de terugwedstrijd
         $liveScore = LiveScoreCup::where('game_id', $game->id)->first();
         if (!$liveScore) {
             Log::error('No live score found for return game', ['game_id' => $game->id]);
             return null;
         }
 
         $liveScoreData = json_decode($liveScore->data, true);
         $secondLegHomeScore = $liveScoreData['home_score'] ?? 0;
         $secondLegAwayScore = $liveScoreData['away_score'] ?? 0;
 
         // Bereken de totale score
         $totalScore = [
             'first_leg_home_score' => $firstLeg->home_score,
             'first_leg_away_score' => $firstLeg->away_score,
             'second_leg_home_score' => $secondLegHomeScore,
             'second_leg_away_score' => $secondLegAwayScore,
             'total_home_score' => $firstLeg->home_score + $secondLegHomeScore,
             'total_away_score' => $firstLeg->away_score + $secondLegAwayScore,
         ];
 
         Log::info('Total score calculated', ['totalScore' => $totalScore]);
 
         return $totalScore;
     }
 
     Log::info('Game is not a return match, no total score calculated', ['game_id' => $game->id]);
     return null;
 }

 protected function processScores(array $scores, array $players, CupGame $game)
{
    $processedScores = [];
    foreach ($scores as $index => $score) {
        $homePlayerName = $this->getPlayerName($score['home_player'] ?? null, $players);
        $awayPlayerName = $this->getPlayerName($score['away_player'] ?? null, $players);

        $processedScores[] = [
            'home_player_name' => $homePlayerName,
            'away_player_name' => $awayPlayerName,
            '1M' => $score['1M'] ?? '',
            '2M' => $score['2M'] ?? '',
            'Belle' => $score['Belle'] ?? '',
            'WinnerId' => $this->determineWinner($score, $score['home_player'], $score['away_player']),
        ];

        MancheCup::updateOrCreate(
            ['game_id' => $game->id, 'number' => $index + 1],
            [
                'player1_id' => $score['home_player'] === 'forfeit' ? null : $score['home_player'],
                'player2_id' => $score['away_player'] === 'forfeit' ? null : $score['away_player'],
                'score1' => $score['1M'] ?? null,
                'score2' => $score['2M'] ?? null,
                'belle_score' => $score['Belle'] ?? null,
                'winner_id' => $this->determineWinner($score, $score['home_player'], $score['away_player']),
            ]
        );
    }
    return $processedScores;
}

protected function processTestMatchScores(array $testmatches, array $players)
{
    $processedTestMatchScores = [];
    foreach ($testmatches as $testmatch) {
        $processedTestMatchScores[] = [
            'home_player_name' => $this->getPlayerName($testmatch['home_player'] ?? null, $players),
            'away_player_name' => $this->getPlayerName($testmatch['away_player'] ?? null, $players),
            '1M' => $testmatch['1M'] ?? '',
        ];
    }
    return $processedTestMatchScores;
}

}