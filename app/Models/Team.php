<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Team extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'captain_id', 'reserve_id', 'division_id', 'club_id', 'location'];
    public function division()
    {
        return $this->belongsTo(Division::class);
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


    public function calculateStatsForSeason($seasonId) {
        $gamesHome = $this->gamesHome()->where('season_id', $seasonId)->get();
        $gamesAway = $this->gamesAway()->where('season_id', $seasonId)->get();
    
        $gamesWon = $gamesHome->where('home_score', '>', 'away_score')->count()
                   + $gamesAway->where('away_score', '>', 'home_score')->count();
        $gamesLost = $gamesHome->where('home_score', '<', 'away_score')->count()
                    + $gamesAway->where('away_score', '<', 'home_score')->count();
        $gamesDraw = $gamesHome->where('home_score', '=', 'away_score')->count()
                    + $gamesAway->where('away_score', '=', 'home_score')->count();
    
        $points = $gamesWon * 3 + $gamesDraw;
    
        return [
            'games_won' => $gamesWon,
            'games_lost' => $gamesLost,
            'games_draw' => $gamesDraw,
            'points' => $points,
        ];
    }

    public static function getSeasonStats($seasonId) {
        $teams = self::with(['gamesHome' => function ($query) use ($seasonId) {
                    $query->where('season_id', $seasonId)
                          ->whereNotNull('home_score')
                          ->whereNotNull('away_score');
                }, 'gamesAway' => function ($query) use ($seasonId) {
                    $query->where('season_id', $seasonId)
                          ->whereNotNull('home_score')
                          ->whereNotNull('away_score');
                }])->get();

        $standings = $teams->map(function ($team) {
            $gamesWon = $team->gamesHome->where('home_score', '>', 'away_score')->count() +
                        $team->gamesAway->where('away_score', '>', 'home_score')->count();
            $gamesLost = $team->gamesHome->where('home_score', '<', 'away_score')->count() +
                         $team->gamesAway->where('away_score', '<', 'home_score')->count();
            $gamesDraw = $team->gamesHome->where('home_score', '=', 'away_score')->count() +
                         $team->gamesAway->where('away_score', '=', 'home_score')->count();
            $points = $gamesWon * 3 + $gamesDraw;

            return [
                'team_id' => $team->id,
                'team_name' => $team->name,
                'games_won' => $gamesWon,
                'games_lost' => $gamesLost,
                'games_draw' => $gamesDraw,
                'points' => $points
            ];
        })->sortByDesc('points')->values();

        return $standings;
    }

    public function games()
    {
        return $this->gamesHome()->union($this->gamesAway()->getQuery());
    }
}

