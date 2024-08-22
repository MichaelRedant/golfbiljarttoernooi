<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

    class Team extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'captain_id', 'reserve_id', 'division_id', 'club_id', 'location'];

    // Bestaande relaties
    public function divisions()
    {
        return $this->belongsToMany(Division::class, 'division_team', 'team_id', 'division_id');
    }

    public function gamesHome()
    {
        return $this->hasMany(Game::class, 'home_team_id');
    }

    public function gamesAway()
    {
        return $this->hasMany(Game::class, 'away_team_id');
    }

    public function players()
    {
        return $this->hasMany(Player::class);
    }

    public function homeGames()
    {
        return $this->hasMany(Game::class, 'home_team_id');
    }

    public function awayGames()
    {
        return $this->hasMany(Game::class, 'away_team_id');
    }

    public function getGamesAttribute()
    {
        return $this->homeGames->merge($this->awayGames);
    }

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    // Nieuwe functie om statistieken op te slaan of bij te werken
    public function updateOrCreateSeasonStats($seasonId, $gamesWon, $gamesLost, $gamesDraw, $points)
    {
        return TeamSeasonStat::updateOrCreate(
            [
                'team_id' => $this->id,
                'season_id' => $seasonId,
            ],
            [
                'games_won' => $gamesWon,
                'games_lost' => $gamesLost,
                'games_draw' => $gamesDraw,
                'points' => $points,
            ]
        );
    }

    // Functie om statistieken voor een seizoen op te halen
    public function getSeasonStats($seasonId)
    {
        return TeamSeasonStat::where('team_id', $this->id)
                             ->where('season_id', $seasonId)
                             ->first();
    }

    // Functie om statistieken voor een seizoen en divisie te berekenen
    public function calculateStatsForSeasonAndDivision($seasonId, $divisionId)
    {
        $gamesWon = 0;
        $gamesLost = 0;
        $gamesDraw = 0;

        $homeGames = $this->gamesHome()->where('season_id', $seasonId)->where('division_id', $divisionId)->get();
        $awayGames = $this->gamesAway()->where('season_id', $seasonId)->where('division_id', $divisionId)->get();

        foreach ($homeGames as $game) {
            if ($game->home_score > $game->away_score) {
                $gamesWon++;
            } elseif ($game->home_score == $game->away_score) {
                $gamesDraw++;
            } else {
                $gamesLost++;
            }
        }

        foreach ($awayGames as $game) {
            if ($game->away_score > $game->home_score) {
                $gamesWon++;
            } elseif ($game->away_score == $game->home_score) {
                $gamesDraw++;
            } else {
                $gamesLost++;
            }
        }

        $points = $gamesWon * 3 + $gamesDraw;

        // Statistieken bijwerken of aanmaken voor het huidige seizoen en divisie
        $this->updateOrCreateSeasonStats($seasonId, $gamesWon, $gamesLost, $gamesDraw, $points);

        return [
            'games_won' => $gamesWon,
            'games_lost' => $gamesLost,
            'games_draw' => $gamesDraw,
            'points' => $points
        ];
    }
}