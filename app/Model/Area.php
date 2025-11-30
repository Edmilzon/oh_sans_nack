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
        'nombre',
    ];

    // Relación a través de area_olimpiada -> area_nivel -> nivel
    // Esta relación es compleja y depende de cómo quieras acceder a los niveles.
    // Lo más directo es a través de AreaOlimpiada.

    public function olimpiadas()
    {
        return $this->belongsToMany(Olimpiada::class, 'area_olimpiada', 'id_area', 'id_olimpiada')
                    ->withTimestamps();
    }

    public function areaOlimpiada()
    {
        return $this->hasMany(AreaOlimpiada::class, 'id_area', 'id_area');
    }
}
