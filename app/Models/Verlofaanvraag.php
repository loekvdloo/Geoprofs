<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Verlofaanvraag extends Model
{
    use HasFactory;
    protected $table = 'verlofaanvraag';

    protected $primaryKey = 'aanvraag_id';
    public $timestamps = false;
    protected $fillable = ['user_id', 'verlof_type_id', 'start_datum', 'eind_datum', 'reden', 'aanvraag_datum', 'status'];
    protected $casts = ['start_datum' => 'date', 'eind_datum' => 'date', 'aanvraag_datum' => 'datetime'];
    public function type()
    {
        return $this->belongsTo(\App\Models\Verloftype::class, 'verlof_type_id', 'verlof_type_id');
    }

    public function medewerker()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'user_id');
    }
}
