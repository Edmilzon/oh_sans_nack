<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Olimpiada extends Model
{
    use HasFactory;

    protected $table = 'olimpiada';
    protected $primaryKey = 'id_olimpiada';

    protected $fillable = [
<<<<<<< HEAD
        'nombre_olimp',
        'gestion_olimp',
        'estado_olimp'
=======
        'nombre_olimp',   // Antes: nombre
        'gestion_olimp',  // Antes: gestion (Ej: "2025")
        'estado_olimp',   // Antes: estado
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1
    ];

    protected $casts = [
        'estado_olimp' => 'boolean',
    ];

    /**
     * RELACIONES DEPENDIENTES (Hijos)
     */

    // Áreas habilitadas para esta gestión (Ej: Matemáticas 2025, Física 2025)
    public function areaOlimpiadas()
    {
        return $this->hasMany(AreaOlimpiada::class, 'id_olimpiada', 'id_olimpiada');
    }

    // Cronogramas de fases para esta gestión (Ej: Cuándo empieza la Distrital en 2025)
    public function cronogramas()
    {
        return $this->hasMany(CronogramaFase::class, 'id_olimpiada', 'id_olimpiada');
    }

    // Configuraciones de acciones permitidas para esta gestión
    public function configuracionesAccion()
    {
        return $this->hasMany(ConfiguracionAccion::class, 'id_olimpiada', 'id_olimpiada');
    }

    // Roles asignados específicamente para esta gestión (Ej: Juan es Evaluador solo en 2025)
    public function usuarioRoles()
    {
        return $this->hasMany(UsuarioRol::class, 'id_olimpiada', 'id_olimpiada');
    }
}