<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inscripcion extends Model
{
    use HasFactory;

    protected $table = 'inscripcion';
    protected $primaryKey = 'id_inscripcion';

    protected $fillable = [
        'id_competidor',
        'id_area_nivel',
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
