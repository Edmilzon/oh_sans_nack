<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccionSistema extends Model
{
    use HasFactory;

    protected $table = 'accion_sistema';
    protected $primaryKey = 'id_accion_sistema'; // Corregido de id_accion

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
    ];

    public function configuraciones()
    {
        // Corregido FK
        return $this->hasMany(ConfiguracionAccion::class, 'id_accion_sistema', 'id_accion_sistema');
    }
}
