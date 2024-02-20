<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = [
        'home_team_id',
        'away_team_id',
        'home_score',
        'away_score',
        'start_time',
        'home_forfeit',
        'away_forfeit',
        'date', // Zorg ervoor dat deze overeenkomt met je tabelstructuur.
        'other_date'
    ];

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
