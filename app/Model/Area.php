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
<<<<<<< HEAD
        'nombre_area', // Antes: 'nombre'
    ];

    /**
     * Relación con las instancias de esta área en diferentes olimpiadas.
     * Ej: "Matemáticas" -> [ "Matemáticas 2024", "Matemáticas 2025" ]
     */
    public function areaOlimpiadas()
    {
        return $this->hasMany(AreaOlimpiada::class, 'id_area', 'id_area');
=======
        'nombre_area',
    ];

    public function olimpiadas()
    {
        return $this->belongsToMany(Olimpiada::class, 'area_olimpiada', 'id_area', 'id_olimpiada')
                    ->withTimestamps();
    }

    public function areasOlimpiada()
    {
        return $this->hasMany(\App\Model\AreaOlimpiada::class, 'id_area');
>>>>>>> 7b7c59242b03600d58a5d1c8f3276e3d5044c776
    }
}
