<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inscripcion extends Model
{
    use HasFactory;

<<<<<<< HEAD
=======
    // Definición de la tabla y clave primaria
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1
    protected $table = 'inscripcion';
    protected $primaryKey = 'id_inscripcion';

    protected $fillable = [
        'id_competidor',
        'id_area_nivel',
<<<<<<< HEAD
        'codigo_inscripcion',
    ];

    public function competidor()
    {
        return $this->belongsTo(Competidor::class, 'id_competidor');
    }

    public function areaNivel()
    {
        return $this->belongsTo(AreaNivel::class, 'id_area_nivel');
    }

    public function evaluaciones()
    {
        return $this->hasMany(Evaluacion::class, 'id_inscripcion');
    }

    public function grupos()
    {
        return $this->belongsToMany(Grupo::class, 'grupo_competidor', 'id_inscripcion', 'id_grupo');
    }

    public function medalleros()
    {
        return $this->hasMany(Medallero::class, 'id_inscripcion');
    }
}
=======
    ];

    /**
     * RELACIONES DIRECTAS (Padres)
     */

    // El estudiante que se inscribe
    public function competidor()
    {
        return $this->belongsTo(Competidor::class, 'id_competidor', 'id_competidor');
    }

    // El Área y Nivel al que se inscribe (Ej: Matemáticas - 5to Secundaria)
    public function areaNivel()
    {
        return $this->belongsTo(AreaNivel::class, 'id_area_nivel', 'id_area_nivel');
    }

    /**
     * RELACIONES DEPENDIENTES (Hijos)
     */

    // Evaluaciones (Notas) que ha recibido en esta inscripción
    public function evaluaciones()
    {
        return $this->hasMany(Evaluacion::class, 'id_inscripcion', 'id_inscripcion');
    }

    // Medallas obtenidas en esta inscripción específica
    public function medalleros()
    {
        return $this->hasMany(Medallero::class, 'id_inscripcion', 'id_inscripcion');
    }

    // Grupos a los que pertenece en esta inscripción (Relación pivote)
    public function grupoCompetidores()
    {
        return $this->hasMany(GrupoCompetidor::class, 'id_inscripcion', 'id_inscripcion');
    }

    // Atajo para obtener los Grupos directamente (Many-to-Many)
    public function grupos()
    {
        return $this->belongsToMany(
            Grupo::class, 
            'grupo_competidor', 
            'id_inscripcion', 
            'id_grupo'
        );
    }
}
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1
