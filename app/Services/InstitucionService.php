<?php

namespace App\Services;

use App\Model\Institucion;
use App\Repositories\InstitucionRepository;
use Illuminate\Database\Eloquent\Collection;
use Exception;

class InstitucionService
{
    public function __construct(
        protected InstitucionRepository $institucionRepository
    ) {}

    public function getAll(): Collection
    {
        return $this->institucionRepository->getAll();
    }

    public function findById(int $id): Institucion
    {
        $institucion = $this->institucionRepository->findById($id);

        if (!$institucion) {
            throw new Exception("Institución no encontrada.", 404);
        }

        return $institucion;
    }

    public function create(array $data): Institucion
    {
        if (isset($data['nombre'])) {
            $data['nombre'] = mb_strtoupper($data['nombre']);
        }
        return $this->institucionRepository->create($data);
    }

    public function update(int $id, array $data): Institucion
    {
        $institucion = $this->findById($id);

        if (isset($data['nombre'])) {
            $data['nombre'] = mb_strtoupper($data['nombre']);
        }

        $this->institucionRepository->update($institucion, $data);
        return $institucion;
    }

    public function delete(int $id): bool
    {
        $institucion = $this->findById($id);
        return $this->institucionRepository->delete($institucion);
    }
}
