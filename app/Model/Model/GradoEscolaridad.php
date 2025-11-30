<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradoEscolaridad extends Model
{
    use HasFactory;

    protected $table = 'grado_escolaridad';
    protected $primaryKey = 'id_grado_escolaridad';

    protected $fillable = ['nombre'];

    public function areaNiveles()
    {
        return $this->hasMany(\App\Model\AreaNivel::class, 'id_grado_escolaridad');
    }
}