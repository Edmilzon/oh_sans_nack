<?php

namespace App\Repositories;

use App\Model\GradoEscolaridad;
use Illuminate\Database\Eloquent\Collection;

class GradoEscolaridadRepository {

    /**
     * Obtiene todos los grados de escolaridad.
     * * Nota: El modelo GradoEscolaridad utiliza 'nombre_grado'.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return GradoEscolaridad::all();
    }

    /**
     * Encuentra un grado de escolaridad por su ID.
     *
     * @param int $id
     * @return GradoEscolaridad|null
     */
    public function findById(int $id): ?GradoEscolaridad
    {
        return GradoEscolaridad::find($id);
    }
}
