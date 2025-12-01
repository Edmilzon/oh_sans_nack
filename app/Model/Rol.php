<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'rol';
    protected $primaryKey = 'id_rol';
    public $timestamps = true;

    protected $fillable = [
        'nombre'
    ];

    public function usuarioRoles()
    {
        return $this->hasMany(UsuarioRol::class, 'id_rol');
    }

    public function rolAcciones()
    {
        return $this->hasMany(RolAccion::class, 'id_rol');
    }

    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'usuario_rol', 'id_rol', 'id_usuario');
    }

    public function accionesSistema()
    {
        return $this->belongsToMany(AccionSistema::class, 'rol_accion', 'id_rol', 'id_accion_sistema');
    }
}