<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'rol';
    protected $primaryKey = 'id_rol';

    protected $fillable = [
        'nombre_rol',
    ];

    /**
     * RELACIONES DIRECTAS (Muchos a Muchos)
     */

    // Usuarios que tienen este rol
    public function usuarios()
    {
        return $this->belongsToMany(
            Usuario::class, 
            'usuario_rol', 
            'id_rol', 
            'id_usuario'
        )
        ->withPivot('id_olimpiada') // Vital: Acceder a qué gestión pertenece la asignación
        ->withTimestamps();
    }

    // Acciones (Permisos) que tiene este rol
    public function acciones()
    {
        return $this->belongsToMany(
            AccionSistema::class, 
            'rol_accion', 
            'id_rol', 
            'id_accion'
        )
        ->withPivot('activo') // Vital: Saber si el permiso está activo o revocado temporalmente
        ->withTimestamps();
    }

    /**
     * RELACIONES CON TABLAS INTERMEDIAS (Pivotes Explícitos)
     * Útiles para consultas avanzadas o mantenimiento.
     */

    public function rolAcciones()
    {
        return $this->hasMany(RolAccion::class, 'id_rol', 'id_rol');
    }

    public function usuarioRoles()
    {
        return $this->hasMany(UsuarioRol::class, 'id_rol', 'id_rol');
    }
}