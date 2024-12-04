<?php

namespace App\Services;

use App\Models\CupGame;
use App\Models\Game;
use App\Models\LiveScore;
use Carbon\Carbon;
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

        // Haal live scores op
        $liveScores = LiveScore::whereIn('game_id', $allGames->pluck('id'))->get()->keyBy('game_id');
        Log::info('Fetched LiveScores:', ['total_live_scores' => $liveScores->count()]);

        $liveData = $allGames->map(function ($game) use ($liveScores) {
            $liveScore = $liveScores->get($game->id);
            $data = $liveScore ? json_decode($liveScore->data, true) : [];

            // Valideer de ontvangen LiveScore data
            if ($liveScore && isset($data['home_score'], $data['away_score'])) {
                // Gebruik live score data als deze beschikbaar is
                $data['home_score'] = $data['home_score'];
                $data['away_score'] = $data['away_score'];
            } else {
                // Fallback naar database scores
                $data['home_score'] = $game->home_score ?? 0;
                $data['away_score'] = $game->away_score ?? 0;
            }

            // Controleer op dubbele testmatch-scores en verwijder deze
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
