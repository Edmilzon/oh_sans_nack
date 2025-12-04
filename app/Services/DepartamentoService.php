<?php

namespace App\Services;

use App\Model\Departamento;
use App\Repositories\DepartamentoRepository;
use Illuminate\Database\Eloquent\Collection;
use Exception;

class DepartamentoService
{
    public function __construct(
        protected DepartamentoRepository $departamentoRepository
    ) {}

    public function listarDepartamentos(): Collection
    {
        return $this->departamentoRepository->getAll();
    }

    public function obtenerDepartamento(int $id): Departamento
    {
        $departamento = $this->departamentoRepository->getById($id);

        if (!$departamento) {
            throw new Exception("Departamento no encontrado.", 404);
        }

        return $departamento;
    }

    public function crearDepartamento(array $data): Departamento
    {
        $data['nombre'] = mb_strtoupper($data['nombre']);

        return $this->departamentoRepository->create($data);
    }

    public function actualizarDepartamento(int $id, array $data): Departamento
    {
        $departamento = $this->obtenerDepartamento($id);

        if (isset($data['nombre'])) {
            $data['nombre'] = mb_strtoupper($data['nombre']);
        }

        $this->departamentoRepository->update($departamento, $data);

        return $departamento;
    }

    public function eliminarDepartamento(int $id): bool
    {
        $departamento = $this->obtenerDepartamento($id);

        return $this->departamentoRepository->delete($departamento);
    }
}
