<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nivel extends Model
{
    use HasFactory;

    protected $table = 'nivel';
    protected $primaryKey = 'id_nivel';

    protected $fillable = [
        'nombre_nivel', // Antes: 'nombre'
    ];

    /**
     * RELACIONES DEPENDIENTES (Hijos)
     */

    // Las configuraciones Área-Nivel que usan este nivel
    public function areaNiveles()
    {
        return $this->hasMany(AreaNivel::class, 'id_nivel', 'id_nivel');
    }
}