<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'location'];

    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    public function divisions()
    {
        // Veronderstellend dat een club meerdere divisies kan hebben
        return $this->hasMany(Division::class);
    }

    
}

