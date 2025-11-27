<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    protected $table = 'login_attempts';
    protected $primaryKey = 'attempt_id';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'attempt_time',
        'attempt_ip',
        'succes',
        'failure_reason',
    ];

    protected $casts = [
        'succes' => 'boolean',
        'attempt_time' => 'datetime',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
