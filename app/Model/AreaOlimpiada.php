<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AreaOlimpiada extends Model
{
    use HasFactory;

    protected $table = 'area_olimpiada';
    protected $primaryKey = 'id_area_olimpiada';

    protected $fillable = [
        'id_area',
        'id_olimpiada',
    ];

    /**
     * RELACIONES DIRECTAS (Padres)
     */

<<<<<<< HEAD
    public function olimpiada() {
        return $this->belongsTo(\App\Model\Olimpiada::class, 'id_olimpiada');
    }

    public function responsablesArea()
    {
        return $this->hasMany(\App\Model\ResponsableArea::class, 'id_area_olimpiada');
    }

    public function areaNiveles()
    {
        return $this->hasMany(AreaNivel::class, 'id_area_olimpiada');
=======
    // Pertenece a un Área (Matemáticas, Física, etc.)
    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area', 'id_area');
    }

    // Pertenece a una Olimpiada específica (Gestión 2024, 2025, etc.)
    public function olimpiada()
    {
        return $this->belongsTo(Olimpiada::class, 'id_olimpiada', 'id_olimpiada');
    }

    /**
     * RELACIONES DEPENDIENTES (Hijos)
     */

    // Los niveles habilitados para esta área en esta olimpiada (AreaNivel)
    public function areaNiveles()
    {
        return $this->hasMany(AreaNivel::class, 'id_area_olimpiada', 'id_area_olimpiada');
    }

    // Responsables asignados a esta área en esta olimpiada
    public function responsablesArea()
    {
        return $this->hasMany(ResponsableArea::class, 'id_area_olimpiada', 'id_area_olimpiada');
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1
    }
}