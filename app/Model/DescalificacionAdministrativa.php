<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DescalificacionAdministrativa extends Model
{
    use HasFactory;

    protected $table = 'descalificacion_administrativa';


    protected $primaryKey = 'id_descalificacion';
    protected $fillable = [
        'id_competidor',
        'observaciones',
    ];

    protected $casts = [
        'fecha_descalificacion' => 'datetime',
    ];

    public function competidor(): BelongsTo
    {
        return $this->belongsTo(Competidor::class, 'id_competidor', 'id_competidor');
    }
}
