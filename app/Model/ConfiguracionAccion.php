<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionAccion extends Model
{
    use HasFactory;

    protected $table = 'configuracion_accion';
    protected $primaryKey = 'id_configuracion_accion';
    public $timestamps = true;

    protected $fillable = [
        'id_area_nivel',
        'id_accion_sistema',
        'id_fase_global',
        'habilitada',
    ];

    protected $casts = [
        'habilitada' => 'boolean',
    ];

    public function areaNivel()
    {
        return $this->belongsTo(AreaNivel::class, 'id_area_nivel', 'id_area_nivel');
    }

    public function faseGlobal()
    {
        return $this->belongsTo(FaseGlobal::class, 'id_fase_global', 'id_fase_global');
    }

    public function accionSistema()
    {
        return $this->belongsTo(AccionSistema::class, 'id_accion_sistema', 'id_accion_sistema');
    }
}
