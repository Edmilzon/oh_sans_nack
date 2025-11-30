<?php

namespace App\Services;

use App\Repositories\GradoEscolaridadRepository;
use App\Model\GradoEscolaridad;
use Illuminate\Database\Eloquent\Collection;

class GradoEscolaridadService
{
    protected $gradoEscolaridadRepository;

    public function __construct(GradoEscolaridadRepository $gradoEscolaridadRepository)
    {
        $this->gradoEscolaridadRepository = $gradoEscolaridadRepository;
    }

    /**
     * Obtiene todos los grados de escolaridad disponibles.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return $this->gradoEscolaridadRepository->getAll();
    }

    /**
     * Busca un grado de escolaridad por su ID.
     *
     * @param int $id
     * @return GradoEscolaridad|null
     */
    public function findById(int $id): ?GradoEscolaridad
    {
        return $this->gradoEscolaridadRepository->findById($id);
    }
}
