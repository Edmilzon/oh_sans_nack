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
        'id_competidor',
        'id_competencia',
        'id_evaluador_an', // Corregido de id_evaluadorAN
        'nota',
        'estado_competidor',
        'observacion', // Corregido de observaciones
        'fecha', // Corregido de fecha_evaluacion
        'estado',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'nota' => 'decimal:2',
        'estado' => 'boolean',
    ];

    public function competencia()
    {
        return $this->belongsTo(Competencia::class, 'id_competencia', 'id_competencia');
    }

    public function competidor()
    {
        return $this->belongsTo(Competidor::class, 'id_competidor', 'id_competidor');
    }

    public function evaluadorAn()
    {
        return $this->belongsTo(EvaluadorAn::class, 'id_evaluador_an', 'id_evaluador_an');
    }

    public function logsCambios()
    {
        // Asumiendo que existe un modelo LogCambioNota
        return $this->hasMany(\App\Model\LogCambioNota::class, 'id_evaluacion', 'id_evaluacion');
    }
}
