<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    use HasFactory;

    protected $table = 'grupo';
    protected $primaryKey = 'id_grupo';

    protected $fillable = [
        'nombre',
        // ELIMINADO: 'id_fase' ya que no existe en la migración final.
        // Si necesitas asociar un grupo a algo, debe ser a través de una relación existente o una tabla pivot.
    ];

    /**
     * The competidores that belong to the grupo.
     */
    public function competidores()
    {
        return $this->belongsToMany(Competidor::class, 'grupo_competidor', 'id_grupo', 'id_competidor')
                    ->withTimestamps();
    }
}
