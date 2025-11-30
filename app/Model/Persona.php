<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;

    protected $table = 'persona';
    protected $primaryKey = 'id_persona';

    protected $fillable = [
        'nombre_pers',   // Antes: nombre
        'apellido_pers', // Antes: apellido
        'ci_pers',       // Antes: ci
        'telefono_pers', // Antes: telefono
        'email_pers',    // Antes: email
    ];

    /**
     * RELACIONES DEPENDIENTES (Hijos)
     */

    // Una persona puede tener un usuario de sistema asociado
    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'id_persona', 'id_persona');
    }

    // Una persona puede estar registrada como competidor
    public function competidor()
    {
        return $this->hasOne(Competidor::class, 'id_persona', 'id_persona');
    }
}