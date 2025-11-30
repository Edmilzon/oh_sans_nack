<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradoEscolaridad extends Model
{
    use HasFactory;

    protected $table = 'grado_escolaridad';
    protected $primaryKey = 'id_grado_escolaridad';

    protected $fillable = [
        'nombre_grado', // Ej: "Quinto de Secundaria"
    ];

    /**
     * RELACIONES DEPENDIENTES (Hijos)
     */

    // Un grado escolar tiene muchos estudiantes (competidores)
    public function competidores()
    {
        return $this->hasMany(Competidor::class, 'id_grado_escolaridad', 'id_grado_escolaridad');
    }
    
    // Un grado escolar pertenece a muchos niveles de grado
    public function nivel_grado()
    {
        return $this->belongsTo(NivelGrado::class, 'id_nivel_grado', 'id_nivel_grado');
    }
}