<?php

// app/Models/CupRound.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CupRound extends Model
{
    use HasFactory;

    protected $fillable = ['cup_id', 'round_name'];

    public function cup()
    {
        return $this->belongsTo(Cup::class);
    }

    public function games()
    {
        return $this->hasMany(CupGame::class);
    }
}
