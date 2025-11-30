<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Model\Rol;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'usuario';
    protected $primaryKey = 'id_usuario';

    protected $fillable = [
        'id_persona',
        'email_usuario',
        'password_usuario',
    ];

    protected $hidden = [
        'password_usuario',
    ];

    public function getAuthPassword()
    {
        return $this->password_usuario;
    }

    /**
     * RELACIONES
     */

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    public function roles()
    {
        return $this->belongsToMany(
            Rol::class,
            'usuario_rol',
            'id_usuario',
            'id_rol'
        )
        ->withPivot('id_olimpiada')
        ->withTimestamps();
    }

    public function responsablesArea()
    {
        return $this->hasMany(ResponsableArea::class, 'id_usuario', 'id_usuario');
    }

    public function evaluadoresAn()
    {
        return $this->hasMany(EvaluadorAn::class, 'id_usuario', 'id_usuario');
    }

    /**
     * MÉTODOS DE UTILIDAD
     */

    // CORRECCIÓN AQUÍ: Agregado '?' antes de 'int'
    public function tieneRol(string $nombreRol, ?int $idOlimpiada = null): bool
    {
        return $this->roles()
            ->where('nombre_rol', $nombreRol)
            ->when($idOlimpiada, function ($query) use ($idOlimpiada) {
                return $query->wherePivot('id_olimpiada', $idOlimpiada);
            })
            ->exists();
    }

    public function asignarRol(string $nombreRol, int $idOlimpiada)
    {
        $rol = Rol::where('nombre_rol', $nombreRol)->firstOrFail();

        $this->roles()->syncWithoutDetaching([
            $rol->id_rol => ['id_olimpiada' => $idOlimpiada]
        ]);
    }
}
