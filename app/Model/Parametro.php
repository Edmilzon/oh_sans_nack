<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parametro extends Model
{
    use HasFactory;

    protected $table = 'parametro';
    protected $primaryKey = 'id_parametro';

    protected $fillable = [
        'nota_min_clasif',
        'cantidad_max_apro',
        'id_area_nivel',
    ];

    protected $casts = [
        'nota_min_clasif' => 'double',
    ];

    /**
     * Get the area_nivel that owns the parametro.
     */
    public function areaNivel()
    {
        return $this->belongsTo(AreaNivel::class, 'id_area_nivel', 'id_area_nivel');
    }
}
