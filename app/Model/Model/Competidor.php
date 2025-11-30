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
        'grado_escolar',
        'departamento',
        'contacto_tutor',
        'id_institucion',
        'id_area_nivel',
        'id_archivo_csv',
        'id_persona',
    ];

    protected $casts = [
        'datos' => 'array',
    ];

    /**
     * Get the institucion that owns the competidor.
     */
    public function institucion()
    {
        return $this->belongsTo(Institucion::class, 'id_institucion', 'id_institucion');
    }

    /**
     * Get the area_nivel that owns the competidor.
     */
    public function areaNivel()
    {
        return $this->belongsTo(AreaNivel::class, 'id_area_nivel', 'id_area_nivel');
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

    /**
     * The grupos that belong to the competidor.
     */
    public function grupos()
    {
        return $this->belongsToMany(Grupo::class, 'grupo_competidor', 'id_competidor', 'id_grupo')
                    ->withTimestamps();
    }

    
}
