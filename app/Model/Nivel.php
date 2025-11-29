<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nivel extends Model
{
    use HasFactory;
    
    protected $table = 'nivel';
    protected $primaryKey = 'id_nivel';
    
    protected $fillable = [
        'nombre',
    ];

    public function areas()
    {
        return $this->belongsToMany(Area::class, 'area_nivel', 'id_nivel', 'id_area')
                    ->withPivot('id_olimpiada', 'activo')
                    ->withTimestamps();
    }

    public function areaNiveles()
    {
        return $this->hasMany(AreaNivel::class, 'id_nivel', 'id_nivel');
    }
}
