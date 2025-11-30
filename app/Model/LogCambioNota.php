<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogCambioNota extends Model
{
    use HasFactory;

    protected $table = 'log_cambio_nota';
    protected $primaryKey = 'id_log_cambio_nota';
    public $timestamps = false;

    protected $fillable = [
        'id_evaluacion',
        'nota_anterior',
        'nota_nueva',
        'fecha_cambio',
    ];

    public function evaluacion()
    {
        return $this->belongsTo(Evaluacion::class, 'id_evaluacion', 'id_evaluacion');
    }
}
