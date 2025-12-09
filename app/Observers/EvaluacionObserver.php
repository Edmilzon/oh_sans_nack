<?php

namespace App\Observers;

use App\Model\Evaluacion;
use App\Model\LogCambioNota;
use Illuminate\Support\Facades\Log;

class EvaluacionObserver
{

    public function updated(Evaluacion $evaluacion): void
    {

        if ($evaluacion->isDirty('nota')) {

            $notaAnterior = $evaluacion->getOriginal('nota');
            $notaNueva = $evaluacion->nota;

            LogCambioNota::create([
                'id_evaluacion' => $evaluacion->id_evaluacion,
                'nota_anterior' => $notaAnterior,
                'nota_nueva'    => $notaNueva,
                'fecha_cambio'  => now(),
            ]);

            Log::info("AUDITORÍA: Nota modificada en Evaluación ID {$evaluacion->id_evaluacion}. Cambio: {$notaAnterior} -> {$notaNueva}");
        }
    }
}
