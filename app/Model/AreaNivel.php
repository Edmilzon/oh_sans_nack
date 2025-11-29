<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AreaNivel extends Model
{
    use HasFactory;

    protected $table = 'area_nivel';
    protected $primaryKey = 'id_area_nivel';

    protected $fillable = [
        'id_area',
        'id_nivel',
        'id_grado_escolaridad',
        'id_olimpiada',
        'activo',
    ];

    public function area()
    {
        return $this->belongsTo(\App\Model\Area::class, 'id_area');
    }

    public function nivel()
    {
        return $this->belongsTo(\App\Model\Nivel::class, 'id_nivel');
    }

    public function gradoEscolaridad()
    {
        return $this->belongsTo(\App\Model\GradoEscolaridad::class, 'id_grado_escolaridad');
    }

    public function olimpiada()
    {
        return $this->belongsTo(\App\Model\Olimpiada::class, 'id_olimpiada');
    }

    public function parametro()
    {
        return $this->hasOne(\App\Model\Parametro::class, 'id_area_nivel');
    }
}
