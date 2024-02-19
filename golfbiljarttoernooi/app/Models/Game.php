<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    public function homeTeam()
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam()
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function manches()
    {
        return $this->hasMany(Manche::class);
    }

    public function belles()
    {
        return $this->hasMany(Belle::class);
    }
    
    use HasFactory;
}
