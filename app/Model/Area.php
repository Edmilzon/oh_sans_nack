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
    }
}
=======
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
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1
