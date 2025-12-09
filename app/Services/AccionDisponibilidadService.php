<?php

namespace App\Services;

use App\Repositories\AccionDisponibilidadRepository;
use Illuminate\Support\Collection;

class AccionDisponibilidadService
{
    public function __construct(
        protected AccionDisponibilidadRepository $repository
    ) {}

    public function listarAcciones(int $idRol, int $idFaseGlobal, int $idGestion): Collection
    {
        return $this->repository->obtenerAccionesHabilitadas($idRol, $idFaseGlobal, $idGestion);
    }
}
