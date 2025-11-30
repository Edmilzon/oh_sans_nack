<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medallero extends Model
{
    use HasFactory;

    protected $table = 'medallero';
    protected $primaryKey = 'id_medallero';

    protected $fillable = [
        'id_inscripcion', // <--- Relación con la inscripción del estudiante
        'id_competencia', // <--- Relación con el examen/competencia
        'puesto_medall',  // Ej: 1, 2, 3
        'medalla_medall', // Ej: 'ORO', 'PLATA', 'BRONCE', 'MENCION'
    ];

    /**
     * RELACIONES DIRECTAS (Padres)
     */

    // La inscripción del estudiante que ganó la medalla
    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class, 'id_inscripcion', 'id_inscripcion');
    }

    // La competencia en la que se obtuvo esta medalla
    public function competencia()
    {
        return $this->belongsTo(Competencia::class, 'id_competencia', 'id_competencia');
    }
}