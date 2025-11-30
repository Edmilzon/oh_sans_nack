<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluadorAn extends Model
{
    use HasFactory;

    protected $table = 'evaluador_an';
    protected $primaryKey = 'id_evaluador_an'; // Corregido de id_evaluadorAN

    protected $fillable = [
        'id_usuario',
        'id_area_nivel',
        'estado', // Agregado
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public function areaNivel()
    {
        return $this->belongsTo(AreaNivel::class, 'id_area_nivel', 'id_area_nivel');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}
