<?php

namespace App\Services;

use App\Model\Olimpiada;
use App\Repositories\CronogramaFaseRepository;
use Illuminate\Support\Carbon;

class SistemaEstadoService
{
    public function __construct(
        protected CronogramaFaseRepository $cronogramaRepo
    ) {}

    public function obtenerEstadoDelSistema(): array
    {
        $anioActual = date('Y');
        $olimpiada = Olimpiada::where('gestion', (string)$anioActual)->first();

        if (!$olimpiada) {
            return [
                'estado_general' => 'inactivo',
                'mensaje' => "No se encontró una olimpiada registrada para la gestión $anioActual",
                'data' => null
            ];
        }

        $cronograma = $this->cronogramaRepo->buscarFaseActiva($olimpiada->id_olimpiada);

        return [
            'estado_general' => 'activo',
            'servidor_fecha' => Carbon::now()->toIso8601String(),
            'gestion' => [
                'id' => $olimpiada->id_olimpiada,
                'nombre' => $olimpiada->nombre,
                'anio' => $olimpiada->gestion,
            ],
            'fase_actual' => $cronograma ? [
                'id_fase_global' => $cronograma->faseGlobal->id_fase_global,
                'nombre' => $cronograma->faseGlobal->nombre,
                'codigo' => $cronograma->faseGlobal->codigo,
                'fecha_cierre' => $cronograma->fecha_fin->toIso8601String(),
                'tiempo_restante' => $cronograma->fecha_fin->diffForHumans(),
            ] : null
        ];
    }
}
