<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fase extends Model
{
    use HasFactory;

    protected $table = 'fase';
    protected $primaryKey = 'id_fase';

    protected $fillable = [
        'nombre',
        'orden',
        'id_area_nivel',
        'id_fase_global',
    ];

    /**
     * Get the area_nivel that owns the fase.
     */
    public function areaNivel()
    {
        return $this->belongsTo(AreaNivel::class, 'id_area_nivel', 'id_area_nivel');
    }

    /**
     * Get the fase_global that owns the fase.
     */
    public function faseGlobal()
    {
        return $this->belongsTo(FaseGlobal::class, 'id_fase_global', 'id_fase_global');
    }

    /**
     * Get the grupos for the fase.
     */
    public function grupos()
    {
        return $this->hasMany(Grupo::class, 'id_fase', 'id_fase');
    }
}
