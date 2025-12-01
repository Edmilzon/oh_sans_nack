<?php

namespace App\Repositories;

use App\Model\Institucion;
use Illuminate\Database\Eloquent\Collection;

class InstitucionRepository
{
    public function getAll(): Collection
    {
        return Institucion::all();
    }

    public function findById(int $id): ?Institucion
    {
        return Institucion::find($id);
    }

    public function create(array $data): Institucion
    {
        return Institucion::create($data);
    }

    public function update(Institucion $institucion, array $data): bool
    {
        return $institucion->update($data);
    }

    public function delete(Institucion $institucion): bool
    {
        return $institucion->delete();
    }
}
