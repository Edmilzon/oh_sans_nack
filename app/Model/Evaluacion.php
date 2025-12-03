<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluacion extends Model
{
    use HasFactory;

    protected $table = 'evaluacion';
    protected $primaryKey = 'id_evaluacion';
    public $timestamps = true;

    protected $fillable = [
        'id_competidor',
        'id_examen_conf',
        'id_evaluador_an',
        'nota',
        'estado_competidor',
        'observacion',
        'fecha',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'nota' => 'decimal:2',
        'estado' => 'boolean',
    ];

    public function examen()
    {
        return $this->belongsTo(ExamenConf::class, 'id_examen_conf', 'id_examen_conf');
    }

    public function competidor()
    {
        return $this->belongsTo(Competidor::class, 'id_competidor', 'id_competidor');
    }

    public function evaluadorAn()
    {
        return $this->belongsTo(EvaluadorAn::class, 'id_evaluador_an', 'id_evaluador_an');
    }

    public function logsCambios()
    {
        return $this->hasMany(LogCambioNota::class, 'id_evaluacion', 'id_evaluacion');
    }
}
