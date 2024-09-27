<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Game extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'home_team_id', 'away_team_id', 'bye_team_id', 'date', 'season_id', 'home_score', 'away_score', 'division_id', 'forfeit_by','home_team_approved',
        'away_team_approved', 'forfeit_confirmed'
    ];

    protected $casts = [
        'date' => 'datetime:d-m-Y',
    ];

    public function setDateAttribute($value)
    {
        $this->attributes['date'] = Carbon::parse($value);
    }


    public function homeTeam()
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam()
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function byeTeam()
    {
        return $this->belongsTo(Team::class, 'bye_team_id');
    }

    public function manches()
    {
        return $this->hasMany(Manche::class,'game_id');
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

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    public function scores()
    {
        return $this->hasMany(Score::class);
    }

    public function liveScore()
{
    return $this->hasOne(LiveScore::class);
}

public function getIsFinishedAttribute()
    {
        // Definieer wat het betekent dat een wedstrijd is afgelopen. Bijvoorbeeld:
        return $this->date->isPast() && $this->manches()->count() > 0;
    }

    public function getCanStartAttribute()
    {
        // Gebruik de GameService om te bepalen of de wedstrijd kan starten
        $gameService = app(\App\Services\GameService::class);
        return $gameService->canStartGame($this);
    }

}
