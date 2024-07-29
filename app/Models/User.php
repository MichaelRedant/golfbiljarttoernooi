<?php
namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasFactory;

    protected $fillable = [
        'name', 'username', 'email', 'password', 'team_id', 'role', 'profile_photo_path', 'team_id'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        // 'password' => 'hashed',
    ];

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isTeam()
    {
        return $this->role === 'team';
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /* public function setPasswordAttribute($password)
    {
        Log::info('Hashing password within User model', ['password' => $password]);
        $this->attributes['password'] = Hash::make($password);
    } */
}

