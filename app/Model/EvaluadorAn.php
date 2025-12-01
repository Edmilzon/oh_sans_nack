<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluadorAn extends Model
{
    use HasFactory;

    protected $table = 'evaluador_an';
    protected $primaryKey = 'id_evaluador_an';
    public $timestamps = true;

    protected $fillable = [
        'id_usuario',
        'id_area_nivel',
        'estado'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function areaNivel()
    {
        return $this->belongsTo(AreaNivel::class, 'id_area_nivel');
    }

    public function evaluaciones()
    {
        return $this->hasMany(Evaluacion::class, 'id_evaluador_an');
    }
}
