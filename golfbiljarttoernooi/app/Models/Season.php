<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    protected $fillable = ['name', 'start_date', 'end_date'];
    protected $casts = [
        'start_date' => 'date',
    ];

    public function games()
    {
        return $this->hasMany(Game::class);
    }
}

