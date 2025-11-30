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

    // V8: Solo credenciales y llave foránea
    protected $fillable = [
        'id_persona',
        'email_usuario',
        'password_usuario',
    ];

    protected $hidden = [
        'password_usuario',
    ];

    // Importante para que Laravel sepa cuál es la contraseña
    public function getAuthPassword()
    {
        return $this->password_usuario;
    }

    /** RELACIONES */

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'usuario_rol', 'id_usuario', 'id_rol')
            ->withPivot('id_olimpiada')
            ->withTimestamps();
    }

    // Helper para asignar roles fácilmente
    public function asignarRol(string $nombreRol, int $idOlimpiada)
    {
        $rol = Rol::where('nombre_rol', $nombreRol)->firstOrFail();
        $this->roles()->syncWithoutDetaching([
            $rol->id_rol => ['id_olimpiada' => $idOlimpiada]
        ]);
    }

    public function tieneRol(string $nombreRol, ?int $idOlimpiada = null): bool
    {
        return $this->roles()
            ->where('nombre_rol', $nombreRol)
            ->when($idOlimpiada, function ($query) use ($idOlimpiada) {
                return $query->wherePivot('id_olimpiada', $idOlimpiada);
            })
            ->exists();
    }
}
