<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'team_id',
        'photo',
        'thumbnail',
        'matches_won',
        'matches_lost',
        'manches_won',
        'manches_lost',
        'club',
        'division_id'
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function divisions()
    {
        return $this->belongsToMany(Division::class, 'player_division');
    }

    public function games()
    {
        return $this->belongsToMany(Game::class, 'game_player')
                    ->withPivot(['manche_1_score', 'manche_2_score', 'belle_score', 'is_belle_winner']);
    }

    public function seasons()
    {
        // Dit haalt alle unieke seizoenen op waarin de speler heeft deelgenomen via games.
        return $this->games()->with('season')->get()->pluck('season')->unique('id');
    }
}
