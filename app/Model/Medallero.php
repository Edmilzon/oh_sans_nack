<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medallero extends Model
{
    use HasFactory;

    protected $table = 'medallero';
    protected $primaryKey = 'id_medallero';
    public $timestamps = true;

    protected $fillable = [
        'id_competidor',
        'id_competencia',
        'puesto',
        'medalla',
    ];

    public function competidor()
    {
        return $this->belongsTo(Competidor::class, 'id_competidor', 'id_competidor');
    }

    public function competencia()
    {
        return $this->belongsTo(Competencia::class, 'id_competencia', 'id_competencia');
    }
}
