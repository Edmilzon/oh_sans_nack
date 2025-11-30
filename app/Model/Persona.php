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
<<<<<<< HEAD
        'nombre_pers', 'apellido_pers', 'ci_pers', 'telefono_pers', 'email_pers'
    ];

=======
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
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1
    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'id_persona', 'id_persona');
    }

<<<<<<< HEAD
=======
    // Una persona puede estar registrada como competidor
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1
    public function competidor()
    {
        return $this->hasOne(Competidor::class, 'id_persona', 'id_persona');
    }
}