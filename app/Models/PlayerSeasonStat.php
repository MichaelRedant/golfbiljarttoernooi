<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerSeasonStat extends Model
{
    use HasFactory;

    // De naam van de database tabel
    protected $table = 'player_season_stats';

    // Mass-assignable velden
    protected $fillable = [
        'player_id',
        'season_id',
        'matches_won',
        'matches_lost',
        'manches_won',
        'manches_lost',
        'points',
        'division_id',
        'matches_played'
    ];
 
    // Relatie naar de speler
    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    // Relatie naar het seizoen
    public function season()
    {
        return $this->belongsTo(Season::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class, 'division_id');
    }
}
