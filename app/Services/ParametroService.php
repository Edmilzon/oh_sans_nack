<?php

namespace App\Services;

use App\Repositories\ParametroRepository;
use Illuminate\Support\Facades\DB;

class ParametroService
{
    public function __construct(
        protected ParametroRepository $repo
    ) {}

    public function guardarParametrosMasivos(array $items): array
    {
        $guardados = [];
        
        DB::transaction(function () use ($items, &$guardados) {
            foreach ($items as $item) {
                $guardados[] = $this->repo->guardarParametro($item);
            }
        });
        
        return $guardados;
    }

    public function getParametrosPorOlimpiada(int $idOlimpiada): array
    {
    $parametros = $this->repo->getByOlimpiada($idOlimpiada);

    $data = $parametros->map(function($p) {
        return [
            'id_parametro' => $p->id_parametro,
            'id_area_nivel' => $p->id_area_nivel,
            'area' => $p->areaNivel->areaOlimpiada->area->nombre ?? 'N/A',
            'nivel' => $p->areaNivel->nivel->nombre ?? 'N/A',
            'nota_minima' => $p->nota_min_aprobacion,
            'cupo_maximo' => $p->cantidad_maxima
        ];
    });

    return [
        'parametros' => $data->toArray(),
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
                    'id_olimpiada' => $i->id_olimpiada,
                    'gestion' => $i->gestion,
                    'nota_minima' => $i->nota_min_aprobacion,
                    'cupo' => $i->cantidad_maxima
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
                'parametros' => $items->map(function($item) {
                    return [
                        'id_area_nivel' => $item->id_area_nivel,
                        'area' => $item->nombre_area,
                        'nivel' => $item->nombre_nivel,
                        'nota_minima' => $item->nota_min_aprobacion,
                        'cupo_maximo' => $item->cantidad_maxima
                    ];
                })
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
        
        $parametros = $all->map(function($p) {
            return [
                'id_parametro' => $p->id_parametro,
                'id_area_nivel' => $p->id_area_nivel,
                'nota_minima' => $p->nota_min_aprobacion,
                'cupo_maximo' => $p->cantidad_maxima,
                'area' => $p->areaNivel->areaOlimpiada->area->nombre ?? 'N/A',
                'nivel' => $p->areaNivel->nivel->nombre ?? 'N/A',
                'gestion' => $p->areaNivel->areaOlimpiada->olimpiada->gestion ?? 'N/A'
            ];
        });
        
        return [
            'parametros' => $parametros,
            'total' => $parametros->count(),
            'message' => 'Todos los parámetros recuperados.'
        ];
    }
}