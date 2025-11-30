<?php

namespace App\Repositories;

use App\Model\Parametro;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class ParametroRepository
{
    public function getAll(): Collection
    {
        // Relación anidada corregida: AreaNivel -> AreaOlimpiada -> Area/Olimpiada
        return Parametro::with([
            'areaNivel',
            'areaNivel.areaOlimpiada.area',
            'areaNivel.nivel',
            'areaNivel.areaOlimpiada.olimpiada'
        ])->get();
    }

    public function getByAreaNivel(int $idAreaNivel): ?Parametro
    {
        // Relación anidada corregida
        return Parametro::with([
            'areaNivel',
            'areaNivel.areaOlimpiada.area',
            'areaNivel.nivel',
            'areaNivel.areaOlimpiada.olimpiada'
        ])
        ->where('id_area_nivel', $idAreaNivel)
        ->first();
    }

    public function getByAreaNiveles(array $idsAreaNivel): Collection
    {
        // Relación anidada corregida
        return Parametro::with([
            'areaNivel',
            'areaNivel.areaOlimpiada.area',
            'areaNivel.nivel',
            'areaNivel.areaOlimpiada.olimpiada'
        ])
        ->whereIn('id_area_nivel', $idsAreaNivel)
        ->get();
    }

    public function getByOlimpiada(int $idOlimpiada): Collection
    {
        \Log::info('ParametroRepository - Buscando parámetros para olimpiada:', ['id_olimpiada' => $idOlimpiada]);

        $parametros = Parametro::with([
                'areaNivel',
                'areaNivel.areaOlimpiada.area',
                'areaNivel.nivel',
                'areaNivel.areaOlimpiada.olimpiada'
            ])
            // Ajustar whereHas para navegar AreaNivel -> AreaOlimpiada -> Olimpiada
            ->whereHas('areaNivel.areaOlimpiada', function(Builder $query) use ($idOlimpiada) {
                $query->where('id_olimpiada', $idOlimpiada);
            })
            ->get();

        \Log::info('ParametroRepository - Resultados encontrados:', [
            'total' => $parametros->count(),
            'parametros_ids' => $parametros->pluck('id_parametro')->toArray()
        ]);

        return $parametros;
    }

    public function create(array $data): Parametro
    {
        // Mapeo de columna 'cantidad_max_apro' a 'cantidad_maxi_param'
        if (isset($data['cantidad_max_apro'])) {
            $data['cantidad_maxi_param'] = $data['cantidad_max_apro'];
            unset($data['cantidad_max_apro']);
        }

        return Parametro::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $parametro = Parametro::find($id);

        if (!$parametro) {
            return false;
        }

        // Mapeo de columna 'cantidad_max_apro' a 'cantidad_maxi_param'
        if (isset($data['cantidad_max_apro'])) {
            $data['cantidad_maxi_param'] = $data['cantidad_max_apro'];
            unset($data['cantidad_max_apro']);
        }

        return $parametro->update($data);
    }

    public function updateOrCreateByAreaNivel(int $idAreaNivel, array $data): Parametro
    {
        // Mapeo de columna 'cantidad_max_apro' a 'cantidad_maxi_param'
        if (isset($data['cantidad_max_apro'])) {
            $data['cantidad_maxi_param'] = $data['cantidad_max_apro'];
            unset($data['cantidad_max_apro']);
        }

        // Mapeo de columna 'nota_min_clasif' a 'nota_min_aprox_param'
        if (isset($data['nota_min_clasif'])) {
            $data['nota_min_aprox_param'] = $data['nota_min_clasif'];
            unset($data['nota_min_clasif']);
        }

        return Parametro::updateOrCreate(
            ['id_area_nivel' => $idAreaNivel],
            $data
        );
    }

    public function delete(int $id): bool
    {
        $parametro = Parametro::find($id);

        if (!$parametro) {
            return false;
        }

        return $parametro->delete();
    }

    public function bulkCreateOrUpdate(array $parametrosData): array
    {
        $results = [];

        foreach ($parametrosData as $data) {
            $results[] = $this->updateOrCreateByAreaNivel(
                $data['id_area_nivel'],
                $data
            );
        }

        return $results;
    }

    public function getAllParametrosByGestiones(): Collection
    {
        return Parametro::join('area_nivel', 'parametro.id_area_nivel', '=', 'area_nivel.id_area_nivel')
            ->join('area_olimpiada', 'area_nivel.id_area_olimpiada', '=', 'area_olimpiada.id_area_olimpiada')
            ->join('area', 'area_olimpiada.id_area', '=', 'area.id_area')
            ->join('nivel', 'area_nivel.id_nivel', '=', 'nivel.id_nivel')
            ->join('olimpiada', 'area_olimpiada.id_olimpiada', '=', 'olimpiada.id_olimpiada')
            ->select([
                'olimpiada.id_olimpiada',
                'olimpiada.gestion_olimp as gestion', // Columna corregida + Alias
                'area_nivel.id_area_nivel',
                'area.nombre_area as nombre_area', // Columna corregida + Alias
                'nivel.nombre_nivel as nombre_nivel', // Columna corregida + Alias
                'parametro.nota_min_aprox_param as nota_minima', // Columna corregida + Alias
                'parametro.cantidad_maxi_param as cant_max_clasificados' // Columna corregida + Alias
            ])
            ->orderBy('olimpiada.gestion_olimp', 'desc') // Columna corregida
            ->orderBy('area.nombre_area') // Columna corregida
            ->orderBy('nivel.nombre_nivel') // Columna corregida
            ->get();
    }

    public function getParametrosByAreaNiveles(array $idsAreaNivel): Collection
    {
        return Parametro::join('area_nivel', 'parametro.id_area_nivel', '=', 'area_nivel.id_area_nivel')
            ->join('area_olimpiada', 'area_nivel.id_area_olimpiada', '=', 'area_olimpiada.id_area_olimpiada')
            ->join('area', 'area_olimpiada.id_area', '=', 'area.id_area')
            ->join('nivel', 'area_nivel.id_nivel', '=', 'nivel.id_nivel')
            ->join('olimpiada', 'area_olimpiada.id_olimpiada', '=', 'olimpiada.id_olimpiada')
            ->whereIn('parametro.id_area_nivel', $idsAreaNivel)
            ->select([
                'olimpiada.id_olimpiada',
                'olimpiada.gestion_olimp as gestion', // Columna corregida + Alias
                'area_nivel.id_area_nivel',
                'area.nombre_area as nombre_area', // Columna corregida + Alias
                'nivel.nombre_nivel as nombre_nivel', // Columna corregida + Alias
                'parametro.nota_min_aprox_param as nota_minima', // Columna corregida + Alias
                'parametro.cantidad_maxi_param as cant_max_clasificados' // Columna corregida + Alias
            ])
            ->orderBy('area_nivel.id_area_nivel')
            ->orderBy('olimpiada.gestion_olimp', 'desc') // Columna corregida
            ->get();
    }
}
