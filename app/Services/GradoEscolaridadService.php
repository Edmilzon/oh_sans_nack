<?php

namespace App\Services;

use App\Model\GradoEscolaridad;
use App\Repositories\GradoEscolaridadRepository;
use Illuminate\Database\Eloquent\Collection;
use Exception;

class GradoEscolaridadService
{
    public function __construct(
        protected GradoEscolaridadRepository $gradoRepository
    ) {}

    public function getAll(): Collection
    {
        return $this->gradoRepository->getAll();
    }

    public function findById(int $id): GradoEscolaridad
    {
        $grado = $this->gradoRepository->findById($id);

        if (!$grado) {
            throw new Exception("Grado de escolaridad no encontrado.", 404);
        }

        return $grado;
    }

    public function create(array $data): GradoEscolaridad
    {
        // Regla de Negocio: Estandarizar nombre a mayúsculas
        if (isset($data['nombre'])) {
            $data['nombre'] = mb_strtoupper($data['nombre']);
        }

        return $this->gradoRepository->create($data);
    }

    public function update(int $id, array $data): GradoEscolaridad
    {
        $grado = $this->findById($id);

        if (isset($data['nombre'])) {
            $data['nombre'] = mb_strtoupper($data['nombre']);
        }

        $this->gradoRepository->update($grado, $data);

        return $grado;
    }

    public function delete(int $id): bool
    {
        $grado = $this->findById($id);
        // Futuro: Aquí validarías si tiene competidores antes de borrar
        return $this->gradoRepository->delete($grado);
    }
}
