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

    /**
     * Obtiene las áreas de las que es responsable un usuario.
     *
     * @param int $idResponsable
     * @return Collection
     */
    public function getAreaPorResponsable(int $idResponsable): Collection
    {
        if ($idResponsable <= 0) {
            return collect();
        }

        // El Repositorio se encarga de la navegación a través de AreaOlimpiada y el mapeo de columnas.
        return $this->medalleroRepository->getAreaPorResponsable($idResponsable);
    }

    /**
     * Obtiene los niveles asociados a un área en la gestión actual,
     * incluyendo los parámetros de medallero.
     *
     * @param int $idArea
     * @return Collection
     */
    public function getNivelesPorArea(int $idArea): Collection
    {
        if ($idArea <= 0) {
            return collect();
        }

        // El Repositorio maneja la navegación AreaNivel -> AreaOlimpiada y el mapeo de medallas.
        return $this->medalleroRepository->getNivelesPorArea($idArea);
    }

    /**
     * Guarda o actualiza los parámetros del medallero para varios niveles.
     *
     * @param array $niveles
     * @return array
     */
    public function guardarMedallero(array $niveles): array
    {
        // El Repositorio maneja la lógica de inserción/actualización con los nombres de columna correctos (e.g., oro_pa_med).
        return $this->medalleroRepository->insertarMedallero($niveles);
    }
}
