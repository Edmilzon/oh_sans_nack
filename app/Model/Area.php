<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;
    
    protected $table = 'area';
    protected $primaryKey = 'id_area';
    
    protected $fillable = [
        'nombre_area',
    ];

    public function olimpiadas()
    {
        return $this->belongsToMany(Olimpiada::class, 'area_olimpiada', 'id_area', 'id_olimpiada')
                    ->withTimestamps();
    }

    public function areasOlimpiada()
    {
        return $this->hasMany(\App\Model\AreaOlimpiada::class, 'id_area');
    }
}
