<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaseGlobal extends Model
{
    use HasFactory;

    protected $table = 'fase_global';
    protected $primaryKey = 'id_fase_global';

    protected $fillable = [
        'codigo_fas_glo',
        'nombre_fas_glo',
        'orden_fas_glo',
    ];

    /**
     * RELACIONES DEPENDIENTES (Hijos)
     */

    // Las competencias (exámenes) que se crean bajo esta fase global
    public function competencias()
    {
        return $this->hasMany(Competencia::class, 'id_fase_global', 'id_fase_global');
    }

    // Los cronogramas que definen cuándo ocurre esta fase en cada olimpiada
    public function cronogramas()
    {
        return $this->hasMany(CronogramaFase::class, 'id_fase_global', 'id_fase_global');
    }

    // Configuración de qué acciones están permitidas durante esta fase
    public function configuracionesAccion()
    {
        return $this->hasMany(ConfiguracionAccion::class, 'id_fase_global', 'id_fase_global');
    }
}