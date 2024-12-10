<?php

// app/Models/CupGame.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CupGame extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';  // Dit is standaard, zorg dat dit klopt
    public $incrementing = true;   // Zorg ervoor dat deze klopt voor jouw database
    protected $keyType = 'int';    // Of 'string' als de ID een string is
    protected $table = 'cup_games';
    protected $fillable = [
    'cup_id',
    'cup_round_id',
    'home_team_id',
    'away_team_id',
    'date',
    'testmatch_score_home',
    'testmatch_score_away'
];

protected $casts = [
    'date' => 'datetime',  // De date wordt als datetime gecast zonder specifieke format
];

    public function homeTeam()
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function cup()
    {
        return $this->belongsTo(Cup::class, 'cup_id');
    }

    public function awayTeam()
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function round()
    {
        return $this->belongsTo(CupRound::class, 'cup_round_id');
    }

    public function liveScoreCup()
    {
        return $this->hasOne(LiveScoreCup::class, 'game_id');
    }
    
    

    public function getIsFinishedAttribute()
    {
        return $this->date->isPast() && $this->manches()->count() > 0;
    }

    public function manches()
    {
        return $this->hasMany(Manche::class, 'game_id');
    }

    public function requiresTestMatch()
{
    // Check of de wedstrijd in de halve finale of finale zit
    if (in_array($this->round->round_name, ['Halve Finale', 'Finale'])) {
        return $this->home_score == $this->away_score;
    }

    // Check voor heen- en terugwedstrijd (1/8 en 1/4 finales)
    if (in_array($this->round->round_name, ['1/8 Finale Terugwedstrijd', '1/4 Finale Terugwedstrijd'])) {
        $firstLegGame = CupGame::where('cup_id', $this->cup_id)
            ->where('home_team_id', $this->away_team_id)
            ->where('away_team_id', $this->home_team_id)
            ->where('cup_round_id', $this->cup_round_id - 1) // Heenwedstrijd
            ->first();

        if ($firstLegGame) {
            $totalHomeScore = $firstLegGame->home_score + $this->home_score;
            $totalAwayScore = $firstLegGame->away_score + $this->away_score;

            return $totalHomeScore == $totalAwayScore;
        }
    }

    return false;
}
}
