<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'usuario';
    protected $primaryKey = 'id_usuario';

    // Ajustado a la V8: Solo credenciales y FK
    protected $fillable = [
        'id_persona',
        'email_usuario',    // Antes: email
        'password_usuario', // Antes: password
    ];

    protected $hidden = [
        'password_usuario', // Ocultamos el hash nuevo
    ];

    /**
     * IMPORTANTE: Sobrescribir métodos de Autenticación de Laravel
     * Porque no usamos la columna estándar 'password'
     */
    public function getAuthPassword()
    {
        return $this->password_usuario;
    }

    /**
     * RELACIONES DIRECTAS (Padres)
     */

    // El perfil con los datos personales (Nombre, CI, Teléfono)
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    /**
     * RELACIONES DIRECTAS (Muchos a Muchos)
     */

    // Roles asignados (con soporte para gestión/olimpiada)
    public function roles()
    {
        return $this->belongsToMany(
            Rol::class, 
            'usuario_rol', 
            'id_usuario', 
            'id_rol'
        )
        ->withPivot('id_olimpiada') // Vital para diferenciar roles por gestión
        ->withTimestamps();
    }

    /**
     * RELACIONES DEPENDIENTES (Hijos)
     */

    // Si es responsable de área en alguna gestión
    public function responsablesArea()
    {
        return $this->hasMany(ResponsableArea::class, 'id_usuario', 'id_usuario');
    }

    // Si es evaluador en alguna gestión
    public function evaluadoresAn()
    {
        return $this->hasMany(EvaluadorAn::class, 'id_usuario', 'id_usuario');
    }

    /**
     * MÉTODOS DE UTILIDAD (Helpers)
     */

    // Verifica si tiene un rol específico (opcionalmente en una gestión)
    public function tieneRol(string $nombreRol, int $idOlimpiada = null): bool
    {
        return $this->roles()
            ->where('nombre_rol', $nombreRol) // Ojo: nombre_rol en V8
            ->when($idOlimpiada, function ($query) use ($idOlimpiada) {
                // Filtramos por la columna pivote
                return $query->wherePivot('id_olimpiada', $idOlimpiada);
            })
            ->exists();
    }

    // Asigna un rol para una gestión específica
    public function asignarRol(string $nombreRol, int $idOlimpiada)
    {
        $rol = Rol::where('nombre_rol', $nombreRol)->firstOrFail();
        
        // syncWithoutDetaching evita duplicados, o puedes usar attach si validas antes
        $this->roles()->syncWithoutDetaching([
            $rol->id_rol => ['id_olimpiada' => $idOlimpiada]
        ]);
    }
}