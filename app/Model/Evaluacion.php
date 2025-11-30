<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluacion extends Model
{
    use HasFactory;

    protected $table = 'evaluacion';
    protected $primaryKey = 'id_evaluacion';

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

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'fecha_evalu' => 'datetime',
        'nota_evalu' => 'decimal:2',
    ];

    /**
     * Get the competencia associated with the evaluacion.
     */
    public function competencia()
    {
        return $this->belongsTo(Competencia::class, 'id_competencia', 'id_competencia');
    }

    /**
     * Get the inscripcion that owns the evaluacion.
     */
    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class, 'id_inscripcion', 'id_inscripcion');
    }

    /**
     * Get the evaluadorAn that owns the evaluacion.
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
