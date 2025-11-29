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

    public function olimpiada()
    {
        return $this->belongsTo(Olimpiada::class, 'id_olimpiada', 'id_olimpiada');
    }

    public function faseGlobal()
    {
        return $this->belongsTo(FaseGlobal::class, 'id_fase_global', 'id_fase_global');
    }

    public function accionSistema()
    {
        return $this->belongsTo(AccionSistema::class, 'id_accion', 'id_accion');
    }
}
