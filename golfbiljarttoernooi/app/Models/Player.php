<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function division()
{
    return $this->belongsTo(Division::class);
}

public function games()
{
    return $this->belongsToMany(Game::class, 'game_player')
                ->withPivot(['manche_1_score', 'manche_2_score', 'belle_score', 'is_belle_winner']);
}


public function getGamesWonAttribute() {
    // Zorg ervoor dat de logica hier correct games berekent die gewonnen zijn
    return $this->games->where('winner_id', $this->id)->count();
}

public function getGamesLostAttribute() {
    // Als de player het 'home_team' was en verloor, of 'away_team' en verloor
    return $this->gamesHome()->where('home_score', '<', 'away_score')
             ->merge($this->gamesAway()->where('away_score', '<', 'home_score'))->count();
}

public function getManchesWonAttribute() {

   
}

public function getManchesLostAttribute() {

}

public function getBellesWonAttribute() {
    // Implementeer logica om het aantal gewonnen belles te berekenen
}

public function getBellesLostAttribute() {
    // Implementeer logica om het aantal verloren belles te berekenen
}


    
    use HasFactory;

    protected $fillable = [
    'first_name',
    'last_name',
    'team_id',
    'division_id',
    'photo',
    'thumbnail',
    'matches_won',
    'matches_lost',
    'manches_won',
    'manches_lost',
    ];
}
