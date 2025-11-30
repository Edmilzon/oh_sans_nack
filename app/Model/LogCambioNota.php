<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogCambioNota extends Model
{
    use HasFactory;

    // Esta tabla no tiene timestamps (created_at/updated_at) gestionados por Laravel,
    // el trigger inserta la fecha.
    public $timestamps = false;

    protected $table = 'log_cambio_nota';
    protected $primaryKey = 'id_log_cambio_nota';

    protected $fillable = [
        'id_evaluacion',
        'nota_nueva',
        'nota_anterior',
        'fecha_cambio',
    ];

    protected $casts = [
        'fecha_cambio' => 'datetime',
        'nota_nueva' => 'decimal:2',
        'nota_anterior' => 'decimal:2',
    ];

    public function evaluacion()
    {
        return $this->belongsTo(Evaluacion::class, 'id_evaluacion', 'id_evaluacion');
    }
}
