<?php

namespace App\Repositories;

use App\Model\Parametro;
use App\Model\Olimpiada;
// Importante: Usar Support\Collection para evitar errores de tipado
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ParametroRepository
{
    public function getAll(): Collection
    {
        return Parametro::with(['areaNivel.area', 'areaNivel.nivel', 'areaNivel.areaOlimpiada.olimpiada'])
            ->get();
    }

    public function getByOlimpiada(int $idOlimpiada): Collection
    {
        // Buscamos parámetros que pertenezcan a area_niveles de esa olimpiada
        // La relación es Parametro -> AreaNivel -> AreaOlimpiada -> Olimpiada
        return Parametro::whereHas('areaNivel.areaOlimpiada', function ($q) use ($idOlimpiada) {
                $q->where('id_olimpiada', $idOlimpiada);
            })
            ->with(['areaNivel.area', 'areaNivel.nivel'])
            ->get();
    }

    /**
     * Guarda o actualiza un parámetro para un area_nivel específico.
     */
    public function guardarParametro(array $data): Parametro
    {
        // Usamos updateOrCreate con los nombres correctos de tu modelo
        return Parametro::updateOrCreate(
            ['id_area_nivel' => $data['id_area_nivel']],
            [
                'nota_min_aprobacion' => $data['nota_min_aprobacion'],
                'cantidad_maxima'     => $data['cantidad_maxima'] ?? null
            ]
        );
    }

    /**
     * Reporte Histórico: Obtiene parámetros de múltiples gestiones.
     */
    public function getParametrosHistoricos(array $idsAreaNivel): Collection
    {
        return DB::table('parametro')
            ->join('area_nivel', 'parametro.id_area_nivel', '=', 'area_nivel.id_area_nivel')
            ->join('area_olimpiada', 'area_nivel.id_area_olimpiada', '=', 'area_olimpiada.id_area_olimpiada')
            ->join('olimpiada', 'area_olimpiada.id_olimpiada', '=', 'olimpiada.id_olimpiada')
            ->join('area', 'area_olimpiada.id_area', '=', 'area.id_area')
            ->join('nivel', 'area_nivel.id_nivel', '=', 'nivel.id_nivel')
            ->whereIn('parametro.id_area_nivel', $idsAreaNivel)
            ->select([
                'olimpiada.id_olimpiada',
                'olimpiada.gestion',
                'area_nivel.id_area_nivel',
                'area.nombre as nombre_area',
                'nivel.nombre as nombre_nivel',
                'parametro.nota_min_aprobacion as nota_minima', // Alias para reporte
                'parametro.cantidad_maxima as cant_max_clasificados'
            ])
            ->orderBy('olimpiada.gestion', 'desc')
            ->get(); // Retorna Support\Collection
    }

    /**
     * Obtiene todos los parámetros agrupados por gestión.
     */
    public function getAllParametrosByGestiones(): Collection
    {
        return DB::table('parametro')
            ->join('area_nivel', 'parametro.id_area_nivel', '=', 'area_nivel.id_area_nivel')
            ->join('area_olimpiada', 'area_nivel.id_area_olimpiada', '=', 'area_olimpiada.id_area_olimpiada')
            ->join('olimpiada', 'area_olimpiada.id_olimpiada', '=', 'olimpiada.id_olimpiada')
            ->join('area', 'area_olimpiada.id_area', '=', 'area.id_area')
            ->join('nivel', 'area_nivel.id_nivel', '=', 'nivel.id_nivel')
            ->select([
                'olimpiada.id_olimpiada',
                'olimpiada.gestion',
                'area.nombre as nombre_area',
                'nivel.nombre as nombre_nivel',
                'parametro.nota_min_aprobacion',
                'parametro.cantidad_maxima'
            ])
            ->orderBy('olimpiada.gestion', 'desc')
            ->get();
    }
}
