<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupoCompetidor extends Model
{
    use HasFactory;

    protected $table = 'grupo_competidor';
    protected $primaryKey = 'id_grupo_competidor';

    protected $fillable = [
        'id_grupo',
        'id_inscripcion',
    ];

    /**
     * RELACIONES DIRECTAS (Padres)
     */

    // El grupo al que pertenece este registro
    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'id_grupo', 'id_grupo');
    }

    // La inscripción del estudiante asignado a este grupo
    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class, 'id_inscripcion', 'id_inscripcion');
    }
}