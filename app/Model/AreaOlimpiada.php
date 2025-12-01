<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AreaOlimpiada extends Model
{
    use HasFactory;

    protected $table = 'area_olimpiada';
    protected $primaryKey = 'id_area_olimpiada';
    public $timestamps = true;

    protected $fillable = [
        'id_area',
        'id_olimpiada',
    ];

    public function area() {
        return $this->belongsTo(Area::class, 'id_area', 'id_area');
    }

    public function olimpiada() {
        return $this->belongsTo(Olimpiada::class, 'id_olimpiada', 'id_olimpiada');
    }

    public function responsableArea()
    {
        return $this->hasOne(ResponsableArea::class, 'id_area_olimpiada', 'id_area_olimpiada');
    }

    public function areaNiveles()
    {
        return $this->hasMany(AreaNivel::class, 'id_area_olimpiada', 'id_area_olimpiada');
    }
}
