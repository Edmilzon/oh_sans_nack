<?php

namespace App\Services;

use App\Repositories\CronogramaFaseRepository;
use App\Model\CronogramaFase;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\OlimpiadaRepository;

class CronogramaFaseService
{
    public function __construct(
        protected CronogramaFaseRepository $repository,
        protected OlimpiadaRepository $olimpiadaRepository
    ) {}

    public function listarTodos(): Collection
    {
        return $this->repository->getAll();
    }

    public function crear(array $data): CronogramaFase
    {
        return $this->repository->create($data);
    }

    public function obtenerPorId(int $id): CronogramaFase
    {
        return $this->repository->find($id);
    }

    public function actualizar(int $id, array $data): CronogramaFase
    {
        return $this->repository->update($id, $data);
    }

    public function eliminar(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function listarVigentes(): Collection
    {
        $olimpiadaActual = $this->olimpiadaRepository->obtenerMasReciente();

        if (!$olimpiadaActual) {
            return new Collection();
        }

        return $this->repository->obtenerPorOlimpiada($olimpiadaActual->id_olimpiada);
    }
}
