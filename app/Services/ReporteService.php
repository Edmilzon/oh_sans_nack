<?php

namespace App\Services;

use App\Repositories\ReporteRepository;
use Carbon\Carbon;

class ReporteService
{
    public function __construct(
        protected ReporteRepository $repository
    ) {}

    public function obtenerHistorial(int $limit, ?int $idArea, ?string $idsNivelesStr, ?string $search = null): array
    {

        $idsNiveles = $idsNivelesStr ? array_map('intval', explode(',', $idsNivelesStr)) : null;

        $paginator = $this->repository->getHistorialCambios($limit, $idArea, $idsNiveles, $search);


        $dataTransformada = collect($paginator->items())->map(function ($item) {

            $esPrimeraCalificacion = (float)$item->nota_anterior == 0;
            $accion = $esPrimeraCalificacion ? 'Calificar' : 'Modificar';

            $descripcion = $esPrimeraCalificacion
                ? "Calificación inicial asignada: {$item->nota_nueva} pts."
                : "Nota modificada de {$item->nota_anterior} a {$item->nota_nueva} pts.";

            return [
                'id_historial'     => $item->id_historial,
                'fecha_hora'       => Carbon::parse($item->fecha_hora)->toIso8601String(),
                'nombre_evaluador' => "{$item->nom_eval} {$item->ape_eval}",
                'nombre_olimpista' => "{$item->nom_olimp} {$item->ape_olimp}",
                'area'             => $item->nombre_area,
                'nivel'            => $item->nombre_nivel,
                'accion'           => $accion,
                'observacion'      => $item->observacion_actual,
                'descripcion'      => $descripcion,
                'id_area'          => $item->id_area,
                'id_nivel'         => $item->id_nivel,
                'nota_anterior'    => (float)$item->nota_anterior,
                'nota_nueva'       => (float)$item->nota_nueva,
            ];
        });

        return [
            'success' => true,
            'data'    => $dataTransformada,
            'meta'    => [
                'total'        => $paginator->total(),
                'page'         => $paginator->currentPage(),
                'limit'        => $paginator->perPage(),
                'totalPages'   => $paginator->lastPage(),
                'hasNextPage'  => $paginator->hasMorePages(),
                'hasPrevPage'  => $paginator->currentPage() > 1
            ]
        ];
    }

    public function listarAreasParaFiltro(): array
    {
        return [
            'success' => true,
            'data' => $this->repository->getAreasSimples()
        ];
    }

    public function listarNivelesDeArea(int $idArea): array
    {
        return [
            'success' => true,
            'data' => $this->repository->getNivelesPorArea($idArea)
        ];
    }
}
