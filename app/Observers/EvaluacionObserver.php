<?php

namespace App\Observers;

use App\Model\Evaluacion;
use App\Model\LogCambioNota;
use Illuminate\Support\Facades\Log;

class EvaluacionObserver
{
    /**
     * Handle the Evaluacion "updated" event.
     * Se ejecuta AUTOMÁTICAMENTE después de guardar un cambio en la BD.
     */
    public function updated(Evaluacion $evaluacion): void
    {
        // 1. Verificamos si el campo 'nota' fue modificado en esta petición.
        // isDirty() es mucho más eficiente que comparar variables manualmente.
        if ($evaluacion->isDirty('nota')) {

            // 2. Obtenemos el valor anterior (antes del save) y el nuevo.
            $notaAnterior = $evaluacion->getOriginal('nota');
            $notaNueva = $evaluacion->nota;

            // 3. Registramos en la tabla de auditoría (LogCambioNota)
            LogCambioNota::create([
                'id_evaluacion' => $evaluacion->id_evaluacion,
                'nota_anterior' => $notaAnterior,
                'nota_nueva'    => $notaNueva,
                'fecha_cambio'  => now(),
            ]);

            // 4. Log del sistema (Opcional pero recomendado para debugging rápido)
            Log::info("AUDITORÍA: Nota modificada en Evaluación ID {$evaluacion->id_evaluacion}. Cambio: {$notaAnterior} -> {$notaNueva}");
        }
    }
}
