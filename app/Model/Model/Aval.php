<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aval extends Model
{
    use HasFactory;

    protected $table = 'aval';
    protected $primaryKey = 'id_aval';

    protected $fillable = [
        'fecha_aval',
        'estado',
        'id_competencia',
        'id_fase',
        'id_responsableArea',
    ];

    public function competencia()
    {
        return $this->belongsTo(Competencia::class, 'id_competencia', 'id_competencia');
    }

    public function fase()
    {
        return $this->belongsTo(Fase::class, 'id_fase', 'id_fase');
    }

    public function responsableArea()
    {
        return $this->belongsTo(ResponsableArea::class, 'id_responsableArea', 'id_responsableArea');
    }
}
