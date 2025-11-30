<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogCambioNota extends Model
{
    use HasFactory;

    protected $table = 'log_cambio_nota';
    protected $primaryKey = 'id_log_cambio_nota';
<<<<<<< HEAD
=======
    
    // Esta tabla se llena automáticamente por el Trigger de base de datos,
    // pero si necesitaras leerla desde Laravel:
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1
    public $timestamps = false;

    protected $fillable = [
        'id_evaluacion',
        'nota_anterior',
        'nota_nueva',
        'fecha_cambio',
    ];

<<<<<<< HEAD
=======
    protected $casts = [
        'nota_anterior' => 'decimal:2',
        'nota_nueva' => 'decimal:2',
        'fecha_cambio' => 'datetime',
    ];

>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1
    public function evaluacion()
    {
        return $this->belongsTo(Evaluacion::class, 'id_evaluacion', 'id_evaluacion');
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1
