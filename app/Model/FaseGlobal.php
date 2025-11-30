<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaseGlobal extends Model
{
    use HasFactory;

    protected $table = 'fase_global';
    protected $primaryKey = 'id_fase_global';

    protected $fillable = [
        'codigo_fas_glo',
        'nombre_fas_glo',
        'orden_fas_glo',
    ];

    public function competencias()
    {
        return $this->hasMany(Competencia::class, 'id_fase_global');
    }

    public function configuracionesAccion()
    {
        return $this->hasMany(ConfiguracionAccion::class, 'id_fase_global');
    }

    public function cronogramas()
    {
        return $this->hasMany(CronogramaFase::class, 'id_fase_global');
    }
}
