<?php

namespace App\Repositories;

use App\Model\Parametro;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ParametroRepository
{
    public function getAll(): Collection
    {
        return Parametro::with(['areaNivel.areaOlimpiada.area', 'areaNivel.nivel', 'areaNivel.areaOlimpiada.olimpiada'])
            ->get();
    }

    public function getByOlimpiada(int $idOlimpiada): Collection
    {
        return Parametro::whereHas('areaNivel.areaOlimpiada', function ($q) use ($idOlimpiada) {
                $q->where('id_olimpiada', $idOlimpiada);
            })
            ->with([
                'areaNivel.areaOlimpiada.area',
                'areaNivel.nivel'
            ])
            ->get();
    }

    public function guardarParametro(array $data): Parametro
    {
        $notaMinAprobacion = isset($data['nota_min_aprobacion']) && $data['nota_min_aprobacion'] !== '' 
            ? $data['nota_min_aprobacion'] 
            : null;
            
        $cantidadMaxima = isset($data['cantidad_maxima']) && $data['cantidad_maxima'] !== '' 
            ? $data['cantidad_maxima'] 
            : PHP_INT_MAX;
        
        if ($cantidadMaxima > 2147483647) {
            $cantidadMaxima = 2147483647;
        }
        
        $parametro = Parametro::updateOrCreate(
            ['id_area_nivel' => $data['id_area_nivel']],
            [
                'nota_min_aprobacion' => $notaMinAprobacion,
                'cantidad_maxima'     => (int)$cantidadMaxima
            ]
        );
        
        $parametro->load(['areaNivel.areaOlimpiada.area', 'areaNivel.nivel']);
        
        return $parametro;
    }

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
                'parametro.id_area_nivel',
                'area.nombre as nombre_area',
                'nivel.nombre as nombre_nivel',
                'parametro.nota_min_aprobacion',
                'parametro.cantidad_maxima'
            ])
            ->orderBy('olimpiada.gestion', 'desc')
            ->get();
    }

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
                'parametro.id_area_nivel',
                'area.nombre as nombre_area',
                'nivel.nombre as nombre_nivel',
                'parametro.nota_min_aprobacion',
                'parametro.cantidad_maxima'
            ])
            ->orderBy('olimpiada.gestion', 'desc')
            ->get();
    }
}