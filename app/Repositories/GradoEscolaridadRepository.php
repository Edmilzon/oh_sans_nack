<?php

namespace App\Repositories;

use App\Model\GradoEscolaridad;
use Illuminate\Database\Eloquent\Collection;

class GradoEscolaridadRepository
{
    public function getAll(): Collection
    {
        return GradoEscolaridad::all();
    }

    public function findById(int $id): ?GradoEscolaridad
    {
        return GradoEscolaridad::find($id);
    }

    public function create(array $data): GradoEscolaridad
    {
        return GradoEscolaridad::create($data);
    }

    public function update(GradoEscolaridad $grado, array $data): bool
    {
        return $grado->update($data);
    }

    public function delete(GradoEscolaridad $grado): bool
    {
        return $grado->delete();
    }
}
