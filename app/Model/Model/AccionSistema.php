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
        'codigo',
        'nombre',
        'descripcion',
    ];

    public function configuraciones()
    {
        return $this->hasMany(ConfiguracionAccion::class, 'id_accion', 'id_accion');
    }
}
