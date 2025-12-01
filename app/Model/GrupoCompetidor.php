<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupoCompetidor extends Model
{
    use HasFactory;

    protected $table = 'grupo_competidor';
    protected $primaryKey = 'id_grupo_competidor';
    public $timestamps = true;

    protected $fillable = [
        'id_grupo',
        'id_competidor',
    ];

    public function grupo() {
        return $this->belongsTo(Grupo::class, 'id_grupo', 'id_grupo');
    }

    public function competidor() {
        return $this->belongsTo(Competidor::class, 'id_competidor', 'id_competidor');
    }
}
