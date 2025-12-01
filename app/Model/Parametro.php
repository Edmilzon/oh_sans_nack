<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parametro extends Model
{
    use HasFactory;

    protected $table = 'parametro';
    protected $primaryKey = 'id_parametro';
    public $timestamps = true;

    protected $fillable = [
        'id_area_nivel',
        'nota_min_aprobacion',
        'cantidad_maxima',
    ];

    protected $casts = [
        'nota_min_aprobacion' => 'decimal:2',
    ];

    public function areaNivel()
    {
        return $this->belongsTo(AreaNivel::class, 'id_area_nivel', 'id_area_nivel');
    }
}