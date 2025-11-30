<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AreaOlimpiada extends Model
{
    use HasFactory;

    protected $table = 'area_olimpiada';
    protected $primaryKey = 'id_area_olimpiada';

    protected $fillable = [
        'id_area',
        'id_olimpiada',
    ];

    public function area() {
        return $this->belongsTo(\App\Model\Area::class, 'id_area');
    }

    public function olimpiada() {
        return $this->belongsTo(\App\Model\Olimpiada::class, 'id_olimpiada');
    }

    public function responsablesArea()
    {
        return $this->hasMany(\App\Model\ResponsableArea::class, 'id_area_olimpiada');
    }

    public function areaNiveles()
    {
        return $this->hasMany(AreaNivel::class, 'id_area_olimpiada');
    }
}