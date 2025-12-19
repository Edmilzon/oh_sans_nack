<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Olimpiada extends Model {
    use HasFactory;

    protected $table = 'olimpiada';
    protected $primaryKey = 'id_olimpiada';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'gestion',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public static $rules = [
        'nombre' => 'required|string|max:255',
        'gestion' => 'required|string|size:4',
    ];

    public static $updateRules = [
        'nombre' => 'string|max:255',
        'gestion' => 'string|size:4',
    ];

    public function areas() {
        return $this->belongsToMany(Area::class, 'area_olimpiada', 'id_olimpiada', 'id_area')->withTimestamps();
    }

    public function areaOlimpiadas() {
        return $this->hasMany(AreaOlimpiada::class, 'id_olimpiada', 'id_olimpiada');

    }
}
