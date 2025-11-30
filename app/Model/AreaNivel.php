<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AreaNivel extends Model
{
    use HasFactory;

    protected $table = 'area_nivel';
    protected $primaryKey = 'id_area_nivel';

    protected $fillable = [
        'id_area_olimpiada', // Corregido: Antes id_area, id_olimpiada por separado
        'id_nivel',
        'es_activo', // Corregido: Antes 'activo'
    ];

    public function areaOlimpiada()
    {
        return $this->belongsTo(AreaOlimpiada::class, 'id_area_olimpiada', 'id_area_olimpiada');
    }

    public function nivel()
    {
        return $this->belongsTo(Nivel::class, 'id_nivel', 'id_nivel');
    }

    public function parametro()
    {
        return $this->hasOne(Parametro::class, 'id_area_nivel', 'id_area_nivel');
    }

    // Relación a través de AreaOlimpiada para llegar al Área
    public function area()
    {
        return $this->hasOneThrough(Area::class, AreaOlimpiada::class, 'id_area_olimpiada', 'id_area', 'id_area_olimpiada', 'id_area');
    }
}
