<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    use HasFactory;

    protected $table = 'departamento';
    protected $primaryKey = 'id_departamento';
    public $timestamps = true;

    protected $fillable = ['nombre'];

    public function competidores()
    {
        return $this->hasMany(Competidor::class, 'id_departamento');
    }
}
