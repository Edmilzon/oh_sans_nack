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
        'id_grado_escolaridad',
        'id_archivo_csv',
        'contacto_tutor_compe',
        'genero_competidor'
    ];

    /**
     * RELACIONES DIRECTAS (Padres)
     */

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

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
