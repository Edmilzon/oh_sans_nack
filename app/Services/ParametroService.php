<?php

namespace App\Services;

use App\Repositories\ParametroRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ParametroService
{
    public function __construct(
        protected ParametroRepository $repo
    ) {}

    public function guardarParametrosMasivos(array $items): void
    {
        DB::transaction(function () use ($items) {
            foreach ($items as $item) {
                $this->repo->guardarParametro($item);
            }
        });
    }

    public function getParametrosPorOlimpiada(int $idOlimpiada): array
    {
        $parametros = $this->repo->getByOlimpiada($idOlimpiada);

        // Formateo para el frontend
        $data = $parametros->map(function($p) {
            return [
                'id_parametro' => $p->id_parametro,
                // Navegación segura
                'area' => $p->areaNivel->areaOlimpiada->area->nombre ?? 'N/A',
                'nivel' => $p->areaNivel->nivel->nombre ?? 'N/A',
                'nota_minima' => $p->nota_min_aprobacion,
                'cupo_maximo' => $p->cantidad_maxima
            ];
        });

        return [
            'parametros' => $data,
            'total' => $data->count()
        ];
    }

    public function getParametrosByAreaNiveles(array $idsAreaNivel): array
    {
        $raw = $this->repo->getParametrosHistoricos($idsAreaNivel);

        $grouped = $raw->groupBy('id_area_nivel');

        $resultado = [];
        foreach ($grouped as $id => $items) {
            $first = $items->first();
            $resultado[] = [
                'area_nivel' => [
                    'id' => $id,
                    'area' => $first->nombre_area,
                    'nivel' => $first->nombre_nivel
                ],
                'historial' => $items->map(fn($i) => [
                    'gestion' => $i->gestion,
                    'nota_minima' => $i->nota_minima,
                    'cupo' => $i->cant_max_clasificados
                ])->values()
            ];
        }

        return $resultado;
    }

    public function getAllParametrosByGestiones(): array
    {
        $raw = $this->repo->getAllParametrosByGestiones();

        $grouped = $raw->groupBy('gestion');

        $resultado = [];
        foreach ($grouped as $gestion => $items) {
            $resultado[] = [
                'gestion' => $gestion,
                'parametros' => $items
            ];
        }

        return [
            'gestiones' => $resultado,
            'total_gestiones' => count($resultado)
        ];
    }

    public function getAllParametros(): array
    {
         $all = $this->repo->getAll();
         return [
             'parametros' => $all,
             'total' => $all->count(),
             'message' => 'Todos los parámetros recuperados.'
         ];
    }
}
