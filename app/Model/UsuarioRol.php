<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UsuarioRol extends Pivot
{
    use HasFactory;

    protected $table = 'usuario_rol';
    protected $primaryKey = 'id_usuario_rol';
    public $timestamps = true;

    protected $fillable = [
        'id_usuario',
        'id_rol'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol');
    }
}
