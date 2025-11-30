<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CronogramaFase extends Model
{
    use HasFactory;

    protected $table = 'cronograma_fase';
    protected $primaryKey = 'id_cronograma';

    protected $fillable = [
        'id_olimpiada',
        'id_fase_global',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function olimpiada()
    {
        return $this->belongsTo(Olimpiada::class, 'id_olimpiada');
    }

    public function faseGlobal()
    {
        return $this->belongsTo(FaseGlobal::class, 'id_fase_global');
    }
}
