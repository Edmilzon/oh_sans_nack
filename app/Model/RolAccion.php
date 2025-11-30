<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolAccion extends Model
{
    use HasFactory;

    protected $table = 'rol_accion';
    protected $primaryKey = 'id_rol_accion';

    protected $fillable = [
        'id_rol',
        'id_accion',
        'activo', // Booleano para activar/desactivar el permiso sin borrar el registro
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * RELACIONES DIRECTAS (Padres)
     */

    // El Rol al que se le asigna el permiso
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    // La Acción del Sistema que se está permitiendo
    public function accionSistema()
    {
        return $this->belongsTo(AccionSistema::class, 'id_accion', 'id_accion');
    }
}