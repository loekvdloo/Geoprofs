<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GebruikerAuth extends Model
{
    protected $table = 'gebruiker_auth';
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'password_hash',
        'auth_provider',
        'mfa_enabled',
        'mfa_type',
        'last_mfa_verified_at',
        'last_login_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
