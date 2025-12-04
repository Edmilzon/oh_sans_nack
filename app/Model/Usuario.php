<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuario';
    protected $primaryKey = 'id_usuario';
    public $timestamps = true;

    protected $fillable = [
        'id_persona',
        'email',
        'password'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    public function usuarioRoles()
    {
        return $this->hasMany(UsuarioRol::class, 'id_usuario');
    }

    public function responsableAreas()
    {
        return $this->hasMany(ResponsableArea::class, 'id_usuario');
    }

    public function evaluadoresAn()
    {
        return $this->hasMany(EvaluadorAn::class, 'id_usuario');
    }

    public function roles()
    {
        // CORRECCIÓN CLAVE: Cargar el campo del pivote y la relación a Olimpiada
        return $this->belongsToMany(Rol::class, 'usuario_rol', 'id_usuario', 'id_rol')
                    ->withPivot('id_olimpiada')
                    ->using(UsuarioRol::class) // Necesario si queremos usar 'olimpiada' en el pivote
                    ->withTimestamps();
    }
}
