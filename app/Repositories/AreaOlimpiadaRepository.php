<?php

namespace App\Repositories;

use App\Model\Area;
use Illuminate\Support\Collection;

class AreaOlimpiadaRepository
{
    /**
     * Encuentra todas las áreas asociadas a una olimpiada específica.
     *
     * @param int $idOlimpiada
     * @return Collection
     */
    public function findAreasByOlimpiadaId(int $idOlimpiada): Collection
    {
        return $this->findAreasBy('id_olimpiada', $idOlimpiada);
    }

    /**
     * Encuentra todas las áreas asociadas a una olimpiada por su gestión.
     *
     * @param string $gestion
     * @return Collection
     */
    public function findAreasByGestion(string $gestion): Collection
    {
        // Mapeo: input 'gestion' -> columna BD 'gestion_olimp'
        return $this->findAreasBy('gestion_olimp', $gestion);
    }

    private function findAreasBy(string $column, $value): Collection
    {
        // Navegación: Area -> hasMany AreaOlimpiada -> belongsTo Olimpiada
        return Area::whereHas('areaOlimpiadas.olimpiada', fn($query) => $query->where("olimpiada.{$column}", $value))
            // Alias para respuesta JSON: nombre_area -> nombre
            ->get(['id_area', 'nombre_area as nombre']);
    }

    public function findAreasByOlimpiadaIdN(int $idOlimpiada): Collection
    {
        return Area::join('area_olimpiada', 'area.id_area', '=', 'area_olimpiada.id_area')
            ->where('area_olimpiada.id_olimpiada', $idOlimpiada)
            ->select('area.id_area', 'area.nombre_area as nombre')
            ->get();
    }

    public function findAreasByGestionN(string $gestion): Collection
    {
        return Area::join('area_olimpiada', 'area.id_area', '=', 'area_olimpiada.id_area')
            ->join('olimpiada', 'area_olimpiada.id_olimpiada', '=', 'olimpiada.id_olimpiada')
            ->where('olimpiada.gestion_olimp', $gestion)
            ->select('area.id_area', 'area.nombre_area as nombre')
            ->get();
    }

    public function findNombresAreasByGestionN(string $gestion): Collection
    {
        // Reutilizamos el método que ya trae los alias correctos
        return $this->findAreasByGestionN($gestion)->pluck('nombre');
    }
}
