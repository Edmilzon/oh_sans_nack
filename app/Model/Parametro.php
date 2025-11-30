<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parametro extends Model
{
    use HasFactory;

    protected $table = 'parametro';
    protected $primaryKey = 'id_parametro';

    protected $fillable = [
        'id_area_nivel',
        'nota_min_aprobacion', // Corregido de nota_min_clasif
        'cantidad_maxima', // Corregido de cantidad_max_apro
    ];

    protected $casts = [
        'nota_min_aprobacion' => 'decimal:2',
    ];

    public function areaNivel()
    {
        return $this->belongsTo(AreaNivel::class, 'id_area_nivel', 'id_area_nivel');
    }
}
