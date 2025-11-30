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

    // Limpiado: La tabla usuario ahora solo tiene referencias y credenciales
    protected $fillable = [
        'id_persona',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'usuario_rol', 'id_usuario', 'id_rol')
                    ->using(UsuarioRol::class)
                    ->withTimestamps();
    }

    public function responsableArea()
    {
        return $this->hasMany(ResponsableArea::class, 'id_usuario', 'id_usuario');
    }

    public function evaluadorAn()
    {
        return $this->hasMany(EvaluadorAn::class, 'id_usuario', 'id_usuario');
    }

    // Métodos helper para acceso a datos de persona
    public function getNombreAttribute() {
        return $this->persona->nombre ?? null;
    }

    public function getApellidoAttribute() {
        return $this->persona->apellido ?? null;
    }
}
