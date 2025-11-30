<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResponsableArea extends Model
{
    use HasFactory;

    protected $table = 'responsable_area';
    protected $primaryKey = 'id_responsableArea';

    protected $fillable = [
        'id_usuario',
        'id_area_olimpiada',
    ];

    public function areaOlimpiada()
    {
        return $this->belongsTo(\App\Model\AreaOlimpiada::class, 'id_area_olimpiada');
    }

    public function area()
    {
        return $this->hasOneThrough(\App\Model\Area::class, \App\Model\AreaOlimpiada::class, 'id_area_olimpiada', 'id_area', 'id_area_olimpiada', 'id_area');
    }
}