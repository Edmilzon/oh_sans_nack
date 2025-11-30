<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NivelGrado extends Model
{
    use HasFactory;

    protected $table = 'nivel_grado';
    protected $primaryKey = 'id_nivel_grado';

    protected $fillable = [
        'id_area_nivel',
        'id_grado_escolaridad',
    ];

    /**
     * RELACIONES DIRECTAS (Padres)
     */

    // Pertenece a una configuración de Área-Nivel (Ej: Matemáticas Secundaria 2025)
    public function areaNivel()
    {
        return $this->belongsTo(AreaNivel::class, 'id_area_nivel', 'id_area_nivel');
    }

    // Pertenece a un Grado Escolar específico (Ej: Quinto de Secundaria)
    public function gradoEscolaridad()
    {
        return $this->belongsTo(GradoEscolaridad::class, 'id_grado_escolaridad', 'id_grado_escolaridad');
    }
}