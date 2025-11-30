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
        'nombre',
        'fecha',
        // 'id_olimpiada', // ELIMINADO: No existe en la migración final de archivo_csv
    ];

    // NOTA: La relación con Olimpiada se eliminó porque la tabla archivo_csv no tiene esa FK en la migración final.

    /**
     * Get the competidores for the archivo_csv.
     */
    public function competidores()
    {
        return $this->hasMany(Competidor::class, 'id_archivo_csv', 'id_archivo_csv');
    }
}
