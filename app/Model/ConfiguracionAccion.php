<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionAccion extends Model
{
    use HasFactory;

    protected $table = 'configuracion_accion';
    protected $primaryKey = 'id_configuracion';

    protected $fillable = [
        'id_olimpiada',
        'id_fase_global',
        'id_accion',
        'habilitada',
    ];

    protected $casts = [
        'habilitada' => 'boolean',
    ];

    /**
     * RELACIONES DIRECTAS (Padres)
     */

    // Pertenece a una Olimpiada (Gestión) específica
    public function olimpiada()
    {
        return $this->belongsTo(Olimpiada::class, 'id_olimpiada', 'id_olimpiada');
    }

    // Pertenece a una Fase Global (ej: "Etapa Clasificatoria")
    public function faseGlobal()
    {
        return $this->belongsTo(FaseGlobal::class, 'id_fase_global', 'id_fase_global');
    }

    // Se refiere a una Acción del Sistema específica (ej: "INSCRIPCION_ESTUDIANTE")
    public function accionSistema()
    {
        return $this->belongsTo(AccionSistema::class, 'id_accion', 'id_accion');
    }
}