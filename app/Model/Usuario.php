<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    use HasFactory;

    protected $table = 'usuario';
    protected $primaryKey = 'id_usuario';
    public $timestamps = true;

    protected $fillable = [
        'id_persona',
        'email',
        'password'
    ];

    protected $hidden = [
        'password'
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
        return $this->belongsToMany(Rol::class, 'usuario_rol', 'id_usuario', 'id_rol');
    }
}
