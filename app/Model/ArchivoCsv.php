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
        'id_olimpiada',
    ];

    /**
     * Get the olimpiada that owns the archivo_csv.
     */
    public function olimpiada()
    {
        return $this->belongsTo(Olimpiada::class, 'id_olimpiada', 'id_olimpiada');
    }

    /**
     * Get the competidores for the archivo_csv.
     */
    public function competidores()
    {
        return $this->hasMany(Competidor::class, 'id_archivo_csv', 'id_archivo_csv');
    }
}
