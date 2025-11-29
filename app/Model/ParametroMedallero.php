<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParametroMedallero extends Model
{
    use HasFactory;

    protected $table = 'param_medallero';
    protected $primaryKey = 'id_param_medallero';

    protected $fillable = [
        'id_area_nivel',
        'oro_pa_med',
        'plata_pa_med',
        'bronce_pa_med',
        'mencion_pa_med',
    ];

    protected $casts = [
        'oro_pa_med' => 'integer',
        'plata_pa_med' => 'integer',
        'bronce_pa_med' => 'integer',
        'mencion_pa_med' => 'integer',
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