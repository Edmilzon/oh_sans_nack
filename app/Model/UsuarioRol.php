<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioRol extends Model
{
    use HasFactory;

    protected $table = 'usuario_rol';
    protected $primaryKey = 'id_usuario_rol';

    protected $fillable = [
        'id_usuario',
        'id_rol',
        'id_olimpiada', // <--- Campo clave para la gestión temporal de roles
    ];

    /**
     * RELACIONES DIRECTAS (Padres)
     */

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function olimpiada()
    {
        return $this->belongsTo(Olimpiada::class, 'id_olimpiada', 'id_olimpiada');
    }
}