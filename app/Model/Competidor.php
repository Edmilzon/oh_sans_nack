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
        'id_archivo_csv',
        'contacto_tutor_compe',
        'genero_competidor',
        'estado_evaluacion',
=======
        'id_grado_escolaridad',
        'id_archivo_csv',
        'contacto_tutor_compe',
        'genero_competidor'
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1
    ];

    /**
     * RELACIONES DIRECTAS (Padres)
     */
<<<<<<< HEAD
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
=======
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

<<<<<<< HEAD
    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'id_competidor');
    }
}
=======
    public function institucion()
    {
        return $this->belongsTo(Institucion::class, 'id_institucion', 'id_institucion');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');
    }

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
    }
}
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1
