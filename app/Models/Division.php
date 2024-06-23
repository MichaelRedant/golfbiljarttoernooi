<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'division_team', 'division_id', 'team_id');
    }

    public function games()
    {
        return $this->hasMany(Game::class, 'division_id');
    }
}
