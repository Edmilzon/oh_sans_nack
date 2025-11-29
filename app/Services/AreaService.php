<?php

namespace App\Services;

use App\Model\Area;
use App\Model\Olimpiada;
use App\Repositories\AreaRepository;
use Illuminate\Support\Facades\Log;

class AreaService {
    protected $areaRepository;

    public function __construct(AreaRepository $areaRepository){
        $this->areaRepository = $areaRepository;
    }

    public function getAreaList(){
        return $this->areaRepository->getAllAreas();
    }

    public function createNewArea(array $data){
        return $this->areaRepository->createArea($data);
    }

    public function getAreasActuales()
    {
        return $this->areaRepository->getAreasByGestion('2025');
    }
}