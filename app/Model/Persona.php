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
        'nombre', 'apellido', 'ci', 'genero', 'telefono', 'email'
    ];

    public function competidor()
    {
        return $this->hasOne(Competidor::class, 'id_persona');
    }
}