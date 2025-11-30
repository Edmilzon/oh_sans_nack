<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccionSistema extends Model
{
    use HasFactory;

    protected $table = 'accion_sistema';
    protected $primaryKey = 'id_accion';

    protected $fillable = [
        'codigo_acc_sis',      // Antes: codigo
        'nombre_acc_sis',      // Antes: nombre
        'descripcion_acc_sis', // Antes: descripcion
    ];

    /**
     * Relación con las configuraciones por fase y gestión.
     * Permite saber si esta acción está habilitada en cierto momento.
     */
    public function configuraciones()
    {
        return $this->hasMany(ConfiguracionAccion::class, 'id_accion', 'id_accion');
    }

    /**
     * Relación con la tabla intermedia explícita RolAccion.
     * Útil si necesitas consultar el estado 'activo' de la asignación.
     */
    public function rolAcciones()
    {
        return $this->hasMany(RolAccion::class, 'id_accion', 'id_accion');
    }

    /**
     * Relación directa con Roles (Muchos a Muchos).
     * Permite obtener todos los roles que tienen esta acción asignada.
     */
    public function roles()
    {
        return $this->belongsToMany(
            Rol::class, 
            'rol_accion', 
            'id_accion', 
            'id_rol'
        )
        ->withPivot('activo')
        ->withTimestamps();
    }
}
