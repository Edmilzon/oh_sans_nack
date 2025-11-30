<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institucion extends Model
{
    use HasFactory;

    protected $table = 'institucion';
    protected $primaryKey = 'id_institucion';

    protected $fillable = [
        'nombre_inst', // Antes: 'nombre'
    ];

    /**
     * RELACIONES DEPENDIENTES (Hijos)
     */

    // Los competidores (estudiantes) que pertenecen a esta institución educativa
    public function competidores()
    {
        return $this->hasMany(Competidor::class, 'id_institucion', 'id_institucion');
    }
}