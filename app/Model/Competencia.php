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
        'id_persona', // Responsable
        'nombre_examen',
        'fecha_inicio',
        'fecha_fin',
        'ponderacion',
        'maxima_nota',
        'es_avalado',
        'estado',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'es_avalado' => 'boolean',
        'estado' => 'boolean',
    ];

    public function faseGlobal()
    {
        return $this->belongsTo(FaseGlobal::class, 'id_fase_global', 'id_fase_global');
    }

    public function areaNivel()
    {
        return $this->belongsTo(AreaNivel::class, 'id_area_nivel', 'id_area_nivel');
    }

    public function responsable()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    public function evaluaciones()
    {
        return $this->hasMany(Evaluacion::class, 'id_competencia', 'id_competencia');
    }

    public function medallero()
    {
        return $this->hasMany(Medallero::class, 'id_competencia', 'id_competencia');
    }
}
