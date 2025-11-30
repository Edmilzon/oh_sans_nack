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
        'id_inscripcion',       // <--- Relación clave con el estudiante inscrito
        'id_competencia',       // <--- Examen que se está calificando
        'id_evaluador_an',      // <--- Profesor que califica
        'nota_evalu',           // <--- La calificación numérica
        'estado_competidor_eva',// <--- Ej: 'CLASIFICADO', 'REPROBADO'
        'observacion_evalu',    // <--- Comentarios opcionales
        'fecha_evalu',
        'estado_evalu'          // <--- Activo/Inactivo (soft delete lógico)
    ];

    protected $casts = [
        'fecha_evalu' => 'datetime',
        'nota_evalu' => 'decimal:2',
        'estado_evalu' => 'boolean',
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

    // El evaluador responsable de esta nota
    public function evaluador()
    {
        return $this->belongsTo(EvaluadorAn::class, 'id_evaluador_an', 'id_evaluador_an');
    }

    /**
     * RELACIONES DEPENDIENTES (Hijos)
     */

    // Historial de cambios de nota (Auditoría)
    public function logsCambios()
    {
        return $this->hasMany(LogCambioNota::class, 'id_evaluacion', 'id_evaluacion');
    }
}