<?php

namespace App\Repositories;

use App\Model\Departamento;
use Illuminate\Database\Eloquent\Collection;

class DepartamentoRepository
{
    public function getAll(): Collection
    {
        return Departamento::all();
    }

    public function getById(int $id): ?Departamento
    {
        return Departamento::find($id);
    }

    public function create(array $data): Departamento
    {
        return Departamento::create($data);
    }

    public function update(Departamento $departamento, array $data): bool
    {
        return $departamento->update($data);
    }

    public function delete(Departamento $departamento): bool
    {
        return $departamento->delete();
    }
}
