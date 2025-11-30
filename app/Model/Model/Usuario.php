<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'usuario';
    protected $primaryKey = 'id_usuario';

    protected $fillable = [
        'nombre',
        'apellido',
        'ci',
        'email',
        'password',
        'telefono',
    ];

    protected $hidden = [
        'password', 
    ];
    
    public function roles()
    {
        return $this->belongsToMany(\App\Model\Rol::class, 'usuario_rol', 'id_usuario', 'id_rol', 'id_usuario', 'id_rol')
                    ->withPivot('id_olimpiada')
                    ->using(\App\Model\UsuarioRol::class)
                    ->withTimestamps();
    }

    public function responsableArea()
    {
        return $this->hasMany(ResponsableArea::class, 'id_usuario', 'id_usuario');
    }

    public function evaluadorAn()
    {
        return $this->hasMany(\App\Model\EvaluadorAn::class, 'id_usuario', 'id_usuario');
    }

    public function asignarRol(string $nombreRol, int $idOlimpiada)
    {
        $rol = Rol::where('nombre', $nombreRol)->firstOrFail();
        $this->roles()->attach($rol->id_rol, ['id_olimpiada' => $idOlimpiada]);
    }

    public function tieneRol(string $nombreRol, int $idOlimpiada = null): bool
    {
        return $this->roles()->where('nombre', $nombreRol)
            ->when($idOlimpiada, function ($query) use ($idOlimpiada) {
                return $query->where('usuario_rol.id_olimpiada', $idOlimpiada);
            })->exists();
    }
}