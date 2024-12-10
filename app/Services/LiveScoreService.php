<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Game;
use App\Models\CupGame;
use App\Models\LiveScore;
use App\Models\LiveScoreCup;
use Illuminate\Support\Facades\Log;

class LiveScoreService
{
    public function getLiveScoresForGames()
{
    Log::info('Fetching live scores for all games.');

    $twoDaysAgo = Carbon::now()->subDays(2)->format('Y-m-d');
    $currentDate = Carbon::now()->format('Y-m-d');

    // Haal reguliere games en cup games binnen de laatste 48 uur op
    $games = Game::with(['homeTeam', 'awayTeam', 'division'])
        ->whereBetween('date', [$twoDaysAgo, $currentDate])
        ->get();

    $cupGames = CupGame::with(['homeTeam', 'awayTeam', 'round'])
        ->whereBetween('date', [$twoDaysAgo, $currentDate])
        ->get();

    $allGames = $games->merge($cupGames);
    Log::info('Fetched all games:', ['total_games' => $allGames->count()]);

    // Haal live scores op uit reguliere en cup live score tabellen
    $liveScores = LiveScore::whereIn('game_id', $games->pluck('id'))->get()->keyBy('game_id');
    $liveScoreCups = LiveScoreCup::whereIn('game_id', $cupGames->pluck('id'))->get()->keyBy('game_id');

    Log::info('Fetched LiveScores:', [
        'regular_live_scores' => $liveScores->count(),
        'cup_live_scores' => $liveScoreCups->count(),
    ]);

    $liveData = $allGames->map(function ($game) use ($liveScores, $liveScoreCups) {
        // Bepaal welke live score tabel te gebruiken
        $liveScore = $game instanceof CupGame
            ? $liveScoreCups->get($game->id)
            : $liveScores->get($game->id);

        $data = $liveScore ? json_decode($liveScore->data, true) : [];

        // Gebruik live score data of fallback naar game data
        $data['home_score'] = $liveScore ? ($data['home_score'] ?? 0) : ($game->home_score ?? 0);
        $data['away_score'] = $liveScore ? ($data['away_score'] ?? 0) : ($game->away_score ?? 0);

        // Controleer op dubbele testmatch-scores en verwijder duplicaten
        if (isset($data['testmatch_scores'])) {
            $data['testmatch_scores'] = collect($data['testmatch_scores'])
                ->uniqueStrict(fn($score) => $score['home_player_name'] . $score['away_player_name'] . $score['1M'])
                ->values()
                ->all();
        }

        // Voeg extra metadata toe
        $data['home_team_name'] = $game->homeTeam->name ?? 'Onbekend';
        $data['away_team_name'] = $game->awayTeam->name ?? 'Onbekend';
        $data['game_date'] = $game->date ? $game->date->format('Y-m-d') : 'Onbekende datum';

        if ($game instanceof CupGame) {
            $data['round_name'] = $game->round->round_name ?? 'Onbekende ronde';
        } else {
            $data['division_name'] = $game->division->name ?? 'Geen divisie';
        }

        // Log de uiteindelijke data
        Log::info('Final live data for game:', [
            'game_id' => $game->id,
            'home_score' => $data['home_score'],
            'away_score' => $data['away_score'],
            'testmatch_scores_count' => count($data['testmatch_scores'] ?? []),
        ]);

        return $data;
    });

    return $liveData;
}

}
