<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AreaNivelGrado extends Pivot
{
    public $timestamps = false;
    public $incrementing = false;

    protected $table = 'area_nivel_grado';

    // Al ser una clave compuesta, no definimos una primaryKey simple estándar

    protected $fillable = [
        'id_area_nivel',
        'id_grado_escolaridad',
    ];

    public function areaNivel()
    {
        return $this->belongsTo(AreaNivel::class, 'id_area_nivel', 'id_area_nivel');
    }

    public function gradoEscolaridad()
    {
        return $this->belongsTo(GradoEscolaridad::class, 'id_grado_escolaridad', 'id_grado_escolaridad');
    }
}
