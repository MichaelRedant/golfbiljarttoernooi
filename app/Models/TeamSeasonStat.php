<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamSeasonStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'season_id',
        'games_won',
        'games_lost',
        'games_draw',
        'points',
    ];

    // Relatie naar Team
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    // Relatie naar Season
    public function season()
    {
        return $this->belongsTo(Season::class);
    }
}
