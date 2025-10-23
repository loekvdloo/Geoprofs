<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Verloftype extends Model {
    protected $table = 'verloftype';
    protected $primaryKey = 'verlof_type_id';
    public $timestamps = false;
    protected $fillable = ['naam','betaald'];
}
