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
        'id_fase',
    ];

    /**
     * Get the fase that owns the grupo.
     */
    public function fase()
    {
        return $this->belongsTo(Fase::class, 'id_fase', 'id_fase');
    }

    /**
     * The competidores that belong to the grupo.
     */
    public function competidores()
    {
        return $this->belongsToMany(Competidor::class, 'grupo_competidor', 'id_grupo', 'id_competidor')
                    ->withTimestamps();
    }
}
