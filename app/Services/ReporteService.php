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
        // Procesar ids_niveles (string "1,2,3" -> array [1,2,3])
        $idsNiveles = $idsNivelesStr ? array_map('intval', explode(',', $idsNivelesStr)) : null;

        $paginator = $this->repository->getHistorialCambios($limit, $idArea, $idsNiveles, $search);

        // CORRECCIÓN: Usamos collect($paginator->items()) en lugar de getCollection()
        // para cumplir con la interfaz LengthAwarePaginator
        $dataTransformada = collect($paginator->items())->map(function ($item) {

            // Lógica de negocio: Determinar Acción
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

        // Estructura de respuesta con Meta datos de paginación
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
