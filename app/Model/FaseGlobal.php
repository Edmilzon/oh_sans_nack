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
        'id_olimpiada',
        'codigo',
        'nombre',
        'orden',
    ];

    public function cronogramas()
    {
        return $this->hasMany(CronogramaFase::class, 'id_fase_global', 'id_fase_global');
    }

    public function competencia()
    {
        return $this->hasMany(Competencia::class, 'id_fase_global', 'id_fase_global');
    }

    public function configuracionAcciones()
    {
        return $this->hasMany(ConfiguracionAccion::class, 'id_fase_global', 'id_fase_global');
    }

    public function olimpiada()
    {
        return $this->belongsTo(Olimpiada::class, 'id_olimpiada', 'id_olimpiada');
    }
}
