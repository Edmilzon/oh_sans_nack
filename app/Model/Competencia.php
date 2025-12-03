<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competencia extends Model
{
    use HasFactory;

    protected $table = 'competencia';
    protected $primaryKey = 'id_competencia';
    public $timestamps = true;

    protected $fillable = [
        'id_fase_global',
        'id_area_nivel',
        'fecha_inicio',
        'fecha_fin',
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

    public function examenes()
    {
        return $this->hasMany(ExamenConf::class, 'id_competencia', 'id_competencia');
    }

    public function evaluaciones()
    {
        // Relación a través de ExamenConf para obtener todas las evaluaciones de una competencia
        return $this->hasManyThrough(Evaluacion::class, ExamenConf::class, 'id_competencia', 'id_examen_conf', 'id_competencia', 'id_examen_conf');
    }

    public function medallero()
    {
        return $this->hasMany(Medallero::class, 'id_competencia', 'id_competencia');
    }
}
