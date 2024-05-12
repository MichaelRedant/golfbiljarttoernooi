<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'home_team_id', 'away_team_id', 'date', 'season_id', 'home_score', 'away_score', 'forfeit'
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
        return $this->hasMany(Belle::class, 'game_id');
    }

    public function players()
    {
        return $this->belongsToMany(Player::class, 'game_player')
                    ->withPivot(['manche_1_score', 'manche_2_score', 'belle_score', 'is_belle_winner']);
    }

    public function season()
    {
        return $this->belongsTo(Season::class);
    }

    protected $casts = [
        'date' => 'datetime:Y-m-d',
    ];

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    public function byeTeam()
{
    return $this->belongsTo(Team::class, 'bye_team_id');
}
}
