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
        'id_archivo_csv',
        'contacto_tutor_compe',
        'genero_competidor',
        'estado_evaluacion',
    ];

    /**
     * RELACIONES DIRECTAS (Padres)
     */
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

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');
    }

    public function inscripciones()
    {
        return $this->hasMany(Inscripcion::class, 'id_competidor');
    }
}
