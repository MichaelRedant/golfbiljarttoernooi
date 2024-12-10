<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveScoreCup extends Model
{
    use HasFactory;

    protected $fillable = ['game_id', 'data'];

    protected $casts = [
        'data' => 'array', // Zorg dat JSON automatisch naar array wordt omgezet
    ];
}

