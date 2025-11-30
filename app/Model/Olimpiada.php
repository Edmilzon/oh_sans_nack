<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Olimpiada extends Model {
    use HasFactory;

    protected $table = 'olimpiada';
    protected $primaryKey = 'id_olimpiada';
    protected $fillable = [
        'nombre',
        'gestion',
        'estado' // Agregado
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

}
