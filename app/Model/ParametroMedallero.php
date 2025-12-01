<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ParametroMedallero extends Model
{
    protected $table = 'param_medallero';
    protected $primaryKey = 'id_param_medallero';
    public $timestamps = true;

    protected $fillable = [
        'id_area_nivel',
        'oro',
        'plata',
        'bronce',
        'mencion'
    ];

    public function areaNivel()
    {
        return $this->belongsTo(AreaNivel::class, 'id_area_nivel', 'id_area_nivel');
    }
}