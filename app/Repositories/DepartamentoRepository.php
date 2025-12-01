<?php

namespace App\Repositories;

use App\Model\Departamento;
use Illuminate\Database\Eloquent\Collection;

class DepartamentoRepository
{
    /**
     * Obtiene todos los departamentos.
     */
    public function getAll(): Collection
    {
        return Departamento::all();
    }

    /**
     * Busca un departamento por su ID.
     */
    public function getById(int $id): ?Departamento
    {
        return Departamento::find($id);
    }

    /**
     * Crea un nuevo departamento.
     */
    public function create(array $data): Departamento
    {
        return Departamento::create($data);
    }

    /**
     * Actualiza un departamento existente.
     */
    public function update(Departamento $departamento, array $data): bool
    {
        return $departamento->update($data);
    }

    /**
     * Elimina un departamento.
     */
    public function delete(Departamento $departamento): bool
    {
        return $departamento->delete();
    }
}
