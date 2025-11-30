<?php

namespace App\Services;

use App\Repositories\MedalleroRepository;
use Illuminate\Support\Collection;

class MedalleroService
{
    protected MedalleroRepository $medalleroRepository;

    public function __construct(MedalleroRepository $medalleroRepository)
    {
        $this->medalleroRepository = $medalleroRepository;
    }

    public function getAreaPorResponsable(int $idResponsable): Collection
    {
        if ($idResponsable <= 0) {
            return collect();
        }

        return $this->medalleroRepository->getAreaPorResponsable($idResponsable);
    }

    public function getNivelesPorArea(int $idArea): Collection
    {
        if ($idArea <= 0) {
            return collect();
        }

        return $this->medalleroRepository->getNivelesPorArea($idArea);
    }

    public function guardarMedallero(array $niveles): array
    {
        return $this->medalleroRepository->insertarMedallero($niveles);
    }
}
