<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResponsableArea extends Model
{
    use HasFactory;

    protected $table = 'responsable_area';
    protected $primaryKey = 'id_responsable_area'; // Corregido de id_responsableArea

    protected $fillable = [
        'id_usuario',
        'id_area_olimpiada',
    ];

    public function areaOlimpiada()
    {
        return $this->belongsTo(AreaOlimpiada::class, 'id_area_olimpiada', 'id_area_olimpiada');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}
