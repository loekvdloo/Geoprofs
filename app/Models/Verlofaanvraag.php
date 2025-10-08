<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Verlofaanvraag extends Model {
    protected $table = 'verlofaanvraag';
    protected $primaryKey = 'aanvraag_id';
    public $timestamps = false;
    protected $fillable = ['medewerker_id','verlof_type_id','start_datum','eind_datum','reden','aanvraag_datum','status'];
    protected $casts = ['start_datum'=>'date','eind_datum'=>'date','aanvraag_datum'=>'datetime'];
}
