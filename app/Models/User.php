<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Verlofaanvraag;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'voornaam',
        'achternaam',
        'email',
        'telefoonnummer',
        'afdeling_id',
        'role_id',
        'account_status',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'account_status' => 'boolean',
        'blocked' => 'boolean',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relaties
    public function afdeling()
    {
        return $this->belongsTo(Afdeling::class, 'afdeling_id', 'afdeling_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    public function authData()
    {
        return $this->hasOne(GebruikerAuth::class, 'user_id', 'user_id');
    }

    public function loginAttempts()
    {
        return $this->hasMany(LoginAttempt::class, 'user_id', 'user_id');
    }

    // Verlof aanvragen relatie
    public function aanvragen()
    {
        return $this->hasMany(Verlofaanvraag::class, 'user_id', 'user_id');
    }

    // Helper om te checken of gebruiker admin is
    public function isAdmin(): bool
    {
        // role_id 1 = Admin
        return (int) $this->role_id === 1;
    }

    // Full name helper
    public function fullName(): string
    {
        return trim($this->voornaam . ' ' . $this->achternaam);
    }
}
