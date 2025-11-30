<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluadorAn extends Model
{
    use HasFactory;

    protected $table = 'evaluador_an';
    protected $primaryKey = 'id_evaluador_an';

    protected $fillable = [
        'id_usuario',
        'id_area_nivel',
        'estado_eva_an',
    ];

    protected $casts = [
        'estado_eva_an' => 'boolean',
    ];

    /**
     * RELACIONES DIRECTAS (Padres)
     */

    // El usuario (profesor) que cumple el rol de evaluador
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function evaluaciones()
    {
        return $this->hasMany(Evaluacion::class, 'id_evaluador_an', 'id_evaluador_an');
    }
}
