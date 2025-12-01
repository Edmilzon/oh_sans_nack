<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AreaNivel extends Model
{
    use HasFactory;

    protected $table = 'area_nivel';
    protected $primaryKey = 'id_area_nivel';
    public $timestamps = true;

    protected $fillable = [
        'id_area_olimpiada',
        'id_nivel',
        'es_activo',
    ];

    public function areaOlimpiada()
    {
        return $this->belongsTo(AreaOlimpiada::class, 'id_area_olimpiada', 'id_area_olimpiada');
    }

    public function nivel()
    {
        return $this->belongsTo(Nivel::class, 'id_nivel', 'id_nivel');
    }

    public function parametro()
    {
        return $this->hasOne(Parametro::class, 'id_area_nivel', 'id_area_nivel');
    }

    public function competidores()
    {
        return $this->hasMany(Competidor::class, 'id_area_nivel');
    }

    public function competencias()
    {
        return $this->hasMany(Competencia::class, 'id_area_nivel');
    }
    // Relación a través de AreaOlimpiada para llegar al Área
    public function area()
    {
        return $this->hasOneThrough(Area::class, AreaOlimpiada::class, 'id_area_olimpiada', 'id_area', 'id_area_olimpiada', 'id_area');
    }

    public function evaluadoresAn()
    {
        return $this->hasMany(EvaluadorAn::class, 'id_area_nivel');
    }

    public function paramMedalleros()
    {
        return $this->hasMany(ParametroMedallero::class, 'id_area_nivel');
    }

    public function configuracionAcciones()
    {
        return $this->hasMany(ConfiguracionAccion::class, 'id_area_nivel');
    }

    public function areaNivelGrados()
    {
        return $this->hasMany(AreaNivelGrado::class, 'id_area_nivel');
    }

    public function gradosEscolaridad()
    {
        return $this->belongsToMany(GradoEscolaridad::class, 'area_nivel_grado', 'id_area_nivel', 'id_grado_escolaridad');
    }
}
