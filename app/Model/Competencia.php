<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competencia extends Model
{
    use HasFactory;

    protected $table = 'competencia';
    protected $primaryKey = 'id_competencia';

    protected $fillable = [
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'id_responsableArea',
        'id_fase',
    ];

    public function responsableArea()
    {
        return $this->belongsTo(ResponsableArea::class, 'id_responsableArea', 'id_responsableArea');
    }

    public function fase()
    {
        return $this->belongsTo(Fase::class, 'id_fase', 'id_fase');
    }

    public function evaluaciones()
    {
        return $this->hasMany(Evaluacion::class, 'id_competencia', 'id_competencia');
    }

    /**
     * Get the medallero for the competencia.
     */
    public function medallero()
    {
        return $this->hasMany(Medallero::class, 'id_competencia', 'id_competencia');
    }
}
