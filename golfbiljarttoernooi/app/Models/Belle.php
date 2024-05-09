<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Belle extends Model
{
    use HasFactory;

    protected $fillable = ['game_id', 'player_id', 'score', 'is_winner'];

    public function game()
    {
        return $this->belongsTo(Game::class, 'game_id');
    }

    public function winner()
    {
        return $this->belongsTo(Player::class, 'winner_id');
    }
}
