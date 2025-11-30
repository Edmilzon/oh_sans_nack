<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    use HasFactory;

    protected $table = 'departamento';
    protected $primaryKey = 'id_departamento';
<<<<<<< HEAD
    
    protected $fillable = ['nombre_dep'];

=======

    protected $fillable = [
        'nombre_dep', // Antes: 'nombre'
    ];

    /**
     * RELACIONES DEPENDIENTES (Hijos)
     */

    // Los competidores (estudiantes) que pertenecen a este departamento
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1
    public function competidores()
    {
        return $this->hasMany(Competidor::class, 'id_departamento', 'id_departamento');
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1
