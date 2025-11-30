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
<<<<<<< HEAD
        'estado_comp',
    ];

    public function faseGlobal()
    {
        return $this->belongsTo(FaseGlobal::class, 'id_fase_global');
    }

    public function areaNivel()
    {
        return $this->belongsTo(AreaNivel::class, 'id_area_nivel');
=======
        'estado_comp'
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'es_avalado' => 'boolean',
        'estado_comp' => 'boolean',
        'ponderacion' => 'decimal:2',
        'maxima_nota' => 'decimal:2',
    ];

    /**
     * RELACIONES DIRECTAS (Padres)
     */

    // Pertenece a una Fase Global (ej: "Etapa Distrital")
    public function faseGlobal()
    {
        return $this->belongsTo(FaseGlobal::class, 'id_fase_global', 'id_fase_global');
    }

    // Pertenece a una configuración Área-Nivel específica
    public function areaNivel()
    {
        return $this->belongsTo(AreaNivel::class, 'id_area_nivel', 'id_area_nivel');
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1
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