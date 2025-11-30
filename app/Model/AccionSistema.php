<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccionSistema extends Model
{
    use HasFactory;

    protected $table = 'accion_sistema';
    protected $primaryKey = 'id_accion';

    protected $fillable = [
        'codigo_acc_sis',
        'nombre_acc_sis',
        'descripcion_acc_sis',
    ];

    /**
     * Relación con las configuraciones por fase y gestión.
     * Permite saber si esta acción está habilitada en cierto momento.
     */
    public function configuraciones()
    {
        return $this->hasMany(ConfiguracionAccion::class, 'id_accion', 'id_accion');
    }

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'rol_accion', 'id_accion', 'id_rol');
    }
}
