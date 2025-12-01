<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    use HasFactory;

    protected $table = 'grupo';
    protected $primaryKey = 'id_grupo';
    public $timestamps = true;

    protected $fillable = [
        'nombre',
    ];

    public function competidores()
    {
        return $this->belongsToMany(Competidor::class, 'grupo_competidor', 'id_grupo', 'id_competidor')
                    ->withTimestamps();
    }

    public function grupoCompetidores()
    {
        return $this->hasMany(GrupoCompetidor::class, 'id_grupo');
    }
}
