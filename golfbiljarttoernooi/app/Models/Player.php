<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function division()
{
    return $this->belongsTo(Division::class);
}
    
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'team_id',
        'division_id',
        'photo',
        'thumbnail',
    ];
}
