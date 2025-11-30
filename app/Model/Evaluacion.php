<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluacion extends Model
{
    use HasFactory;

    protected $table = 'evaluacion';
    protected $primaryKey = 'id_evaluacion';

    // Campos permitidos para asignación masiva
    protected $fillable = [
        'id_inscripcion',
        'id_competencia',
        'id_evaluador_an',
        'nota_evalu',
        'estado_competidor_eva',
        'observacion_evalu',
        'fecha_evalu',
        'estado_evalu',
    ];

    protected $casts = [
        'fecha_evalu' => 'datetime',
        'nota_evalu' => 'decimal:2',
    ];

    /**
     * RELACIONES DIRECTAS (Padres)
     */

    // La inscripción específica que está siendo evaluada
    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class, 'id_inscripcion', 'id_inscripcion');
    }

    // El examen o competencia al que corresponde esta nota
    public function competencia()
    {
        return $this->belongsTo(Competencia::class, 'id_competencia', 'id_competencia');
    }

    /**
     * RELACIONES DEPENDIENTES (Hijos)
     */
    public function evaluadorAn()
    {
        return $this->belongsTo(EvaluadorAn::class, 'id_evaluador_an', 'id_evaluador_an');
    }

    /**
     * Get the log de cambios de nota for the evaluacion.
     */
    public function logCambiosNota()
    {
        return $this->hasMany(LogCambioNota::class, 'id_evaluacion', 'id_evaluacion');
    }
}
