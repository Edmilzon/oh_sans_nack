<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nivel extends Model
{
    use HasFactory;
    
    protected $table = 'nivel';
    protected $primaryKey = 'id_nivel';
    public $timestamps = true;
    
    protected $fillable = [
        'nombre',
    ];

    public function areaNiveles()
    {
        return $this->hasMany(AreaNivel::class, 'id_nivel');
    }
}
