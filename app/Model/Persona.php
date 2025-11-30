<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;

    protected $table = 'persona';
    protected $primaryKey = 'id_persona';

    // Se eliminó 'genero' ya que en la migración está en 'competidor'
    protected $fillable = [
        'nombre', 'apellido', 'ci', 'telefono', 'email'
    ];

    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'id_persona', 'id_persona');
    }

    public function competidores()
    {
        return $this->hasMany(Competidor::class, 'id_persona', 'id_persona');
    }
}
