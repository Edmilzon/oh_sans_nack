<?php

namespace App\Services;

use App\Model\Area;
use App\Model\Olimpiada;
use App\Repositories\AreaRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;

class AreaService {
    protected $areaRepository;

    public function __construct(AreaRepository $areaRepository){
        $this->areaRepository = $areaRepository;
    }

    /**
     * Obtiene todas las áreas registradas.
     * @return Collection
     */
    public function getAreaList(): Collection
    {
        return $this->areaRepository->getAllAreas();
    }

    /**
     * Crea una nueva área.
     * @param array $data Contiene el nombre del área.
     * @return Area
     */
    public function createNewArea(array $data): Area
    {
        // El Repositorio ya maneja el mapeo de 'nombre' a 'nombre_area'
        return $this->areaRepository->createArea($data);
    }

    /**
     * Obtiene las áreas que están asignadas a la olimpiada de la gestión actual (año en curso).
     * @return Collection
     */
    public function getAreasActuales(): Collection
    {
        $gestionActual = date('Y');
        // El Repositorio filtra por gestion_olimp y retorna el alias 'nombre'
        return $this->areaRepository->getAreasByGestion($gestionActual);
    }
}
