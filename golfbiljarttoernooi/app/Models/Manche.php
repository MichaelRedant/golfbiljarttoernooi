<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manche extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id', 
        'player1_id', 
        'player2_id', 
        'score1', 
        'score2', 
        'belle_score', 
        'winner_id', 
        'number'
    ];

    public function game()
    {
        return $this->belongsTo(Game::class, 'game_id');
    }

    public function winner()
    {
        return $this->belongsTo(Player::class, 'winner_id');
    }

    public function player1()
    {
        return $this->belongsTo(Player::class, 'player1_id');
    }

    public function player2()
    {
        return $this->belongsTo(Player::class, 'player2_id');
    }
}