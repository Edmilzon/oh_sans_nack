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
        'puesto',
        'medalla',
        'id_competidor',
        'id_competencia',
    ];

    /**
     * Get the competidor that won the medal.
     */
    public function competidor()
    {
        return $this->belongsTo(Competidor::class, 'id_competidor', 'id_competidor');
    }

    /**
     * Get the competencia for the medal.
     */
    public function competencia()
    {
        return $this->belongsTo(Competencia::class, 'id_competencia', 'id_competencia');
    }
}
