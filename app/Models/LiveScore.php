<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveScore extends Model
{
    protected $fillable = ['game_id', 'data'];

    protected $casts = [
        'data' => 'array', // Zorg ervoor dat de JSON-gegevens automatisch worden gecast naar een array
    ];
}

