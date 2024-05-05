<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Team extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'captain_id', 'reserve_id'];
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
}

