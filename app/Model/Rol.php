<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; 

class Rol extends Model {
    use HasFactory;
    
    protected $table = 'rol';
    protected $primaryKey = 'id_rol'; 
    protected $fillable = [
        'nombre'
    ];
}