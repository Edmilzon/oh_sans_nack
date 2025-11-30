<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    use HasFactory;

    protected $table = 'grupo';
    protected $primaryKey = 'id_grupo';

    protected $fillable = [
        'nombre_grupo', // Ej: "Grupo A - Laboratorio 1"
    ];

    /**
     * RELACIONES DEPENDIENTES (Hijos)
     */

    // Relación directa con la tabla intermedia (útil para operaciones de pivote)
    public function grupoCompetidores()
    {
        return $this->hasMany(GrupoCompetidor::class, 'id_grupo', 'id_grupo');
    }

    // Relación Muchos a Muchos con Inscripciones
    // Permite acceder directamente a los estudiantes del grupo: $grupo->inscripciones
    public function inscripciones()
    {
        return $this->belongsToMany(
            Inscripcion::class, 
            'grupo_competidor', // Tabla pivote
            'id_grupo',         // FK de este modelo en la pivote
            'id_inscripcion'    // FK del otro modelo en la pivote
        )
        ->withTimestamps();
    }
}