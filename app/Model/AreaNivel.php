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

<<<<<<< HEAD
    protected $casts = [
        'es_activo_area_nivel' => 'boolean',
    ];

    /**
     * RELACIONES DIRECTAS (Padres)
     */

    // Pertenece a una configuración de Área en una Olimpiada específica
    public function areaOlimpiada()
    {
        return $this->belongsTo(AreaOlimpiada::class, 'id_area_olimpiada', 'id_area_olimpiada');
=======
    public function areaOlimpiada()
    {
        return $this->belongsTo(AreaOlimpiada::class, 'id_area_olimpiada');
>>>>>>> 7b7c59242b03600d58a5d1c8f3276e3d5044c776
    }

    // Pertenece a un Nivel Académico (Primaria, Secundaria, etc.)
    public function nivel()
    {
<<<<<<< HEAD
        return $this->belongsTo(Nivel::class, 'id_nivel', 'id_nivel');
    }

    /**
     * RELACIONES DEPENDIENTES (Hijos)
     */

    // Las inscripciones de estudiantes a este nivel específico
    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'id_area_nivel', 'id_area_nivel');
    }

    // Los exámenes (competencias) creados para este nivel
    public function competencias()
    {
        return $this->hasMany(Competencia::class, 'id_area_nivel', 'id_area_nivel');
=======
        return $this->belongsTo(Nivel::class, 'id_nivel');
>>>>>>> 7b7c59242b03600d58a5d1c8f3276e3d5044c776
    }

    // Los evaluadores asignados para calificar en este nivel
    public function evaluadores()
    {
        return $this->hasMany(EvaluadorAn::class, 'id_area_nivel', 'id_area_nivel');
    }

    // Parámetros de configuración (nota mínima, cantidad de clasificados)
    public function parametro()
    {
<<<<<<< HEAD
        return $this->hasOne(Parametro::class, 'id_area_nivel', 'id_area_nivel');
=======
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
>>>>>>> 7b7c59242b03600d58a5d1c8f3276e3d5044c776
    }

    // Configuración de medallas para este nivel
    public function parametroMedallero()
    {
        return $this->hasOne(ParametroMedallero::class, 'id_area_nivel', 'id_area_nivel');
    }
}
