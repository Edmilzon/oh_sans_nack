<?php

namespace App\Repositories;

use App\Model\Area;
use Illuminate\Support\Collection;

class AreaOlimpiadaRepository
{

    public function findAreasByOlimpiadaId(int $idOlimpiada): Collection
    {
        return $this->findAreasBy('id_olimpiada', $idOlimpiada);
    }

    public function findAreasByGestion(string $gestion): Collection
    {
        return $this->findAreasBy('gestion', $gestion);
    }

    private function findAreasBy(string $column, $value): Collection
    {
        return Area::whereHas('olimpiadas', fn($query) => $query->where("olimpiada.{$column}", $value))
            ->get(['id_area', 'nombre']);
    }
    
    public function findAreasByOlimpiadaIdN(int $idOlimpiada): Collection
    {
        return Area::join('area_olimpiada', 'area.id_area', '=', 'area_olimpiada.id_area')
            ->where('area_olimpiada.id_olimpiada', $idOlimpiada)
            ->select('area.id_area', 'area.nombre')
            ->get();
    }

    public function findAreasByGestionN(string $gestion): Collection
    {
        return Area::join('area_olimpiada', 'area.id_area', '=', 'area_olimpiada.id_area')
            ->join('olimpiada', 'area_olimpiada.id_olimpiada', '=', 'olimpiada.id_olimpiada')
            ->where('olimpiada.gestion', $gestion)
            ->select('area.id_area', 'area.nombre')
            ->get();
    }

    public function findNombresAreasByGestionN(string $gestion): Collection
    {
        return $this->findAreasByGestion($gestion)->pluck('nombre');
    }
}
