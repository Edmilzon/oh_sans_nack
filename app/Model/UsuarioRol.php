<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UsuarioRol extends Pivot
{
    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'usuario_rol';

    public function olimpiada() {
        return $this->belongsTo(Olimpiada::class, 'id_olimpiada');
    }
}
