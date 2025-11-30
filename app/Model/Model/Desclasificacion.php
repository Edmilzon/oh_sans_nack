<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Desclasificacion extends Model
{
    use HasFactory;

    protected $table = 'desclasificacion';
    protected $primaryKey = 'id_desclasificacion';

    protected $fillable = [
        'fecha',
        'motivo',
        'id_competidor',
        'id_evaluacion',
    ];

    /**
     * Get the competidor that was desclasificado.
     */
    public function competidor()
    {
        return $this->belongsTo(Competidor::class, 'id_competidor', 'id_competidor');
    }

    /**
     * Get the evaluacion associated with the desclasificacion.
     */
    public function evaluacion()
    {
        return $this->belongsTo(Evaluacion::class, 'id_evaluacion', 'id_evaluacion');
    }
}
