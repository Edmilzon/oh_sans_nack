<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $table = 'area';
    protected $primaryKey = 'id_area';

    protected $fillable = [
        'nombre_area', // Antes: 'nombre'
    ];

    /**
     * Relación con las instancias de esta área en diferentes olimpiadas.
     * Ej: "Matemáticas" -> [ "Matemáticas 2024", "Matemáticas 2025" ]
     */
    public function areaOlimpiadas()
    {
        return $this->hasMany(AreaOlimpiada::class, 'id_area', 'id_area');
    }
}