<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchivoCsv extends Model
{
    use HasFactory;

    protected $table = 'archivo_csv';
    protected $primaryKey = 'id_archivo_csv';

    protected $fillable = [
<<<<<<< HEAD
        'nombre_arc_csv', // Antes: 'nombre'
        'fecha_arc_csv',  // Antes: 'fecha'
    ];

    protected $casts = [
        'fecha_arc_csv' => 'date',
    ];

    /**
     * Obtiene los competidores que fueron importados mediante este archivo.
=======
        'nombre_arc_csv',
        'fecha_arc_csv',
    ];

    /**
     * Get the competidores for the archivo_csv.
>>>>>>> 7b7c59242b03600d58a5d1c8f3276e3d5044c776
     */
    public function competidores()
    {
        return $this->hasMany(Competidor::class, 'id_archivo_csv', 'id_archivo_csv');
    }
}
