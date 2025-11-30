<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competidor extends Model
{
    use HasFactory;

    protected $table = 'competidor';
    protected $primaryKey = 'id_competidor';

    protected $fillable = [
        'id_persona',
        'id_institucion',
        'id_departamento',
<<<<<<< HEAD
        'id_grado_escolaridad',
        'id_archivo_csv',
        'contacto_tutor_compe',
        'genero_competidor'
=======
        'id_archivo_csv',
        'contacto_tutor_compe',
        'genero_competidor',
        'estado_evaluacion',
>>>>>>> 7b7c59242b03600d58a5d1c8f3276e3d5044c776
    ];

    /**
     * RELACIONES DIRECTAS (Padres)
     */
<<<<<<< HEAD
=======
    public function institucion()
    {
        return $this->belongsTo(Institucion::class, 'id_institucion', 'id_institucion');
    }

    /**
     * Get the archivo_csv that owns the competidor.
     */
    public function archivoCsv()
    {
        return $this->belongsTo(ArchivoCsv::class, 'id_archivo_csv', 'id_archivo_csv');
    }
>>>>>>> 7b7c59242b03600d58a5d1c8f3276e3d5044c776

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

<<<<<<< HEAD
    public function institucion()
    {
        return $this->belongsTo(Institucion::class, 'id_institucion', 'id_institucion');
    }

=======
>>>>>>> 7b7c59242b03600d58a5d1c8f3276e3d5044c776
    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');
    }

<<<<<<< HEAD
    public function gradoEscolaridad()
    {
        return $this->belongsTo(GradoEscolaridad::class, 'id_grado_escolaridad', 'id_grado_escolaridad');
    }

    public function archivoCsv()
    {
        return $this->belongsTo(ArchivoCsv::class, 'id_archivo_csv', 'id_archivo_csv');
    }

    /**
     * RELACIONES DEPENDIENTES (Hijos)
     */

    // Un competidor puede tener múltiples inscripciones (ej: Matemáticas y Física)
    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'id_competidor', 'id_competidor');
=======
    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'id_competidor');
>>>>>>> 7b7c59242b03600d58a5d1c8f3276e3d5044c776
    }
}
