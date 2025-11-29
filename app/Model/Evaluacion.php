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
        'nota',
        'observaciones',
        'fecha_evaluacion',
        'estado',
        'id_competidor',
        'id_competencia',
        'id_evaluadorAN',
        'id_parametro',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'fecha_evaluacion' => 'datetime',
        'nota' => 'decimal:2',
    ];

    /**
     * Get the competencia associated with the evaluacion.
     */
    public function competencia()
    {
        return $this->belongsTo(Competencia::class, 'id_competencia', 'id_competencia');
    }

    /**
     * Get the competidor that owns the evaluacion.
     */
    public function competidor()
    {
        return $this->belongsTo(Competidor::class, 'id_competidor', 'id_competidor');
    }

    /**
     * Get the evaluadorAn that owns the evaluacion.
     */
    public function evaluadorAn()
    {
        return $this->belongsTo(EvaluadorAn::class, 'id_evaluadorAN', 'id_evaluadorAN');
    }

    /**
     * Get the parametro associated with the evaluacion.
     */
    public function parametro()
    {
        return $this->belongsTo(Parametro::class, 'id_parametro', 'id_parametro');
    }
}
