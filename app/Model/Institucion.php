<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institucion extends Model
{
    use HasFactory;

    protected $table = 'institucion';
    protected $primaryKey = 'id_institucion';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
    ];

    public function competidores()
    {
        return $this->hasMany(Competidor::class, 'id_institucion', 'id_institucion');
    }
}
