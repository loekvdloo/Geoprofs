<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
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

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'account_status' => 'boolean',
        'blocked' => 'boolean',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    public function aanvragen()
    {
        return $this->hasMany(Verlofaanvraag::class, 'medewerker_id');
    }
    public function isAdmin(): bool
    {
        // Voor dit project: role_id 1 = Admin (RoleSeeder)
        return (int)$this->role_id === 1;
    }
}
