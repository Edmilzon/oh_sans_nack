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
        // Aquí podrías agregar validaciones de negocio extra si hicieran falta
        // (ej: verificar si la olimpiada está activa, etc.)

        return $this->repository->obtenerAccionesHabilitadas($idRol, $idFaseGlobal, $idGestion);
    }
}
