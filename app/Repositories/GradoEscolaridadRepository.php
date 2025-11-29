<?php

namespace App\Repositories;

use App\Model\GradoEscolaridad;
use Illuminate\Database\Eloquent\Collection;

class GradoEscolaridadRepository {

     public function getAll(): Collection
    {
        return GradoEscolaridad::all();
    }

    public function findById(int $id): ?GradoEscolaridad
    {
        return GradoEscolaridad::find($id);
    }
}