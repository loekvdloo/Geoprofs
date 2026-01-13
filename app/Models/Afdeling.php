<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Afdeling extends Model
{
    // Geef expliciet de juiste tabelnaam
    protected $table = 'afdeling';
    protected $primaryKey = 'afdeling_id';
    public $timestamps = false;

    protected $fillable = ['afdeling_naam'];

    public function users()
    {
        return $this->hasMany(User::class, 'afdeling_id', 'afdeling_id');
    }
}
