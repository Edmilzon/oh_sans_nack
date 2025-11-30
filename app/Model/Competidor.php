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
        'id_archivo_csv',
        'id_institucion',
        'id_departamento',
        'id_area_nivel',
        'id_persona',
        'id_grado_escolaridad', // Agregado según migración
        'contacto_tutor',
        'genero',
    ];

    public function institucion()
    {
        return $this->belongsTo(Institucion::class, 'id_institucion', 'id_institucion');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');
    }

    public function areaNivel()
    {
        return $this->belongsTo(AreaNivel::class, 'id_area_nivel', 'id_area_nivel');
    }

    public function gradoEscolaridad()
    {
        return $this->belongsTo(GradoEscolaridad::class, 'id_grado_escolaridad', 'id_grado_escolaridad');
    }

    public function archivoCsv()
    {
        return $this->belongsTo(ArchivoCsv::class, 'id_archivo_csv', 'id_archivo_csv');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    public function grupos()
    {
        return $this->belongsToMany(Grupo::class, 'grupo_competidor', 'id_competidor', 'id_grupo')
                    ->withTimestamps();
    }
}
