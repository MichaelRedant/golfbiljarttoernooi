<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = ['name','division_id'];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    // in Team model

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

    public function reserves()
    {
        return $this->hasMany(ReservePlayer::class);
    }
    
    use HasFactory;
}
