<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = ['name','division_id'];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function players()
    {
        return $this->hasMany(Player::class);
    }

    public function reserves()
    {
        return $this->hasMany(ReservePlayer::class);
    }
    
    use HasFactory;
}
