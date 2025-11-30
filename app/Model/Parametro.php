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
        'nota_min_aprox_param',
        'cantidad_maxi_param',
    ];

    protected $casts = [
        'nota_min_aprox_param' => 'decimal:2',
        'cantidad_maxi_param' => 'integer',
    ];

    /**
     * RELACIONES DIRECTAS (Padres)
     */

    // Pertenece a una configuración de Área-Nivel específica
    public function areaNivel()
    {
        return $this->belongsTo(AreaNivel::class, 'id_area_nivel', 'id_area_nivel');
    }
}