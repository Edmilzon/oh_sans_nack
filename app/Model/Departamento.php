<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    use HasFactory;

    protected $table = 'departamento'; // nombre correcto
    protected $primaryKey = 'id_departamento';
    
    protected $fillable = ['nombre_dep'];

    public function competidores()
    {
        return $this->hasMany(Competidor::class, 'id_departamento', 'id_departamento');
    }
}
