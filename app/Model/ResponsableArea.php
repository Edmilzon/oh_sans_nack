<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResponsableArea extends Model
{
    use HasFactory;

    protected $table = 'responsable_area';
    protected $primaryKey = 'id_responsable_area';

    protected $fillable = [
        'id_usuario',
        'id_area_olimpiada',
    ];

    /**
     * RELACIONES DIRECTAS (Padres)
     */

    // El usuario (persona) que ha sido designado como responsable
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    // La configuración de Área+Gestión de la que es responsable
    public function areaOlimpiada()
    {
        return $this->belongsTo(AreaOlimpiada::class, 'id_area_olimpiada', 'id_area_olimpiada');
    }

    /**
     * ACCESORES ÚTILES
     * Para acceder directamente al Área sin pasar manualmente por AreaOlimpiada
     */
    public function getAreaAttribute()
    {
        return $this->areaOlimpiada ? $this->areaOlimpiada->area : null;
    }
}