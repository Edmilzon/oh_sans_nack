<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competencia extends Model
{
    use HasFactory;

    protected $table = 'competencia';
    protected $primaryKey = 'id_competencia';

    protected $fillable = [
        'id_fase_global',
        'id_area_nivel',
        'nombre_examen',
        'fecha_inicio',
        'fecha_fin',
        'ponderacion',
        'maxima_nota',
        'es_avalado',
        'estado_comp',
    ];

    public function faseGlobal()
    {
        return $this->belongsTo(FaseGlobal::class, 'id_fase_global');
    }

    public function areaNivel()
    {
        return $this->belongsTo(AreaNivel::class, 'id_area_nivel');
    }

    /**
     * RELACIONES DEPENDIENTES (Hijos)
     */

    // Tiene muchas evaluaciones (notas de estudiantes)
    public function evaluaciones()
    {
        return $this->hasMany(Evaluacion::class, 'id_competencia', 'id_competencia');
    }

    // Tiene un medallero asociado (Resultados finales)
    public function medalleros()
    {
        return $this->hasMany(Medallero::class, 'id_competencia', 'id_competencia');
    }
}
