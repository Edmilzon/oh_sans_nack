<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CronogramaFase extends Model
{
    use HasFactory;

    protected $table = 'cronograma_fase';
    protected $primaryKey = 'id_cronograma';

    protected $fillable = [
        'id_olimpiada',
        'id_fase_global',
        'fecha_inicio',
        'fecha_fin',
        'estado' // 'Pendiente', 'En Curso', 'Finalizada'
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    // --- ACCESORES (Virtuales) ---

    /**
     * Verifica si esta fase está activa en este preciso momento.
     * Útil para el frontend: $cronograma->esta_activa
     */
    public function getEstaActivaAttribute(): bool
    {
        $ahora = Carbon::now();
        return $this->estado === 'En Curso' && 
               $ahora->between($this->fecha_inicio, $this->fecha_fin);
    }

    // --- RELACIONES ---

    // La gestión a la que pertenece (Ej: Gestión 2025)
    public function olimpiada()
    {
        return $this->belongsTo(Olimpiada::class, 'id_olimpiada', 'id_olimpiada');
    }

    // La definición global de la fase (Ej: "Etapa Distrital")
    public function faseGlobal()
    {
        return $this->belongsTo(FaseGlobal::class, 'id_fase_global', 'id_fase_global');
    }
}