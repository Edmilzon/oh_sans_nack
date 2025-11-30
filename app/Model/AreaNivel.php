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
        'id_area_olimpiada',
        'id_nivel',
        'es_activo_area_nivel',
    ];

    public function areaOlimpiada()
    {
        return $this->belongsTo(AreaOlimpiada::class, 'id_area_olimpiada');
    }

    // Pertenece a un Nivel Académico (Primaria, Secundaria, etc.)
    public function nivel()
    {
        return $this->belongsTo(Nivel::class, 'id_nivel');
    }

    // Los evaluadores asignados para calificar en este nivel
    public function evaluadores()
    {
        return $this->hasMany(EvaluadorAn::class, 'id_area_nivel', 'id_area_nivel');
    }

    // Parámetros de configuración (nota mínima, cantidad de clasificados)
    public function parametro()
    {
        return $this->hasOne(Parametro::class, 'id_area_nivel');
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'id_area_nivel');
    }

    public function grupos()
    {
        return $this->hasMany(Grupo::class, 'id_area_nivel');
    }

    public function competencias()
    {
        return $this->hasMany(Competencia::class, 'id_area_nivel');
    }

    public function evaluadoresAn()
    {
        return $this->hasMany(EvaluadorAn::class, 'id_area_nivel');
    }

    // Configuración de medallas para este nivel
    public function parametroMedallero()
    {
        return $this->hasOne(ParametroMedallero::class, 'id_area_nivel', 'id_area_nivel');
    }
}
