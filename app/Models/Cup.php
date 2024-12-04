<?php

// app/Models/Cup.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cup extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'season_id', 'division_id'];

    public function rounds()
    {
        return $this->hasMany(CupRound::class);
    }

    public function games()
    {
        return $this->hasMany(CupGame::class, 'cup_id');
    }
    
    public function teams()
{
    return $this->belongsToMany(Team::class, 'cup_team', 'cup_id', 'team_id');
}

    public function season()
    {
        return $this->belongsTo(Season::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}
