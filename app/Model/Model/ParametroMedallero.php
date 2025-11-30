<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ParametroMedallero extends Model
{
    protected $table = 'param_medallero';
    protected $primaryKey = 'id_param_medallero';
    protected $fillable = [
        'id_area_nivel',
        'oro',
        'plata',
        'bronce',
        'menciones'
    ];
}



