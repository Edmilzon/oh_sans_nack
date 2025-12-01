<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradoEscolaridad extends Model
{
    use HasFactory;

    protected $table = 'grado_escolaridad';
    protected $primaryKey = 'id_grado_escolaridad';
    public $timestamps = true;

    protected $fillable = ['nombre'];

    public function areaNiveles()
    {
    return $this->belongsToMany(
        AreaNivelGrado::class,
        'area_nivel_grado',
        'id_grado_escolaridad',
        'id_area_nivel'
    );
    }

    public function competidores()
    {
        return $this->hasMany(\App\Model\Competidor::class, 'id_grado_escolaridad');
    }
}