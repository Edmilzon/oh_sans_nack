<?php

namespace App\Repositories;

use App\Model\Nivel;
use Illuminate\Database\Eloquent\Collection;

class NivelRepository {

    /**
     * Obtiene todos los niveles existentes.
     *
     * @return Collection
     */
    public function getAllNivel(): Collection
    {
        return Nivel::all();
    }

    /**
     * Crea un nuevo nivel.
     *
     * @param array $data Contiene el campo 'nombre' del frontend.
     * @return Nivel
     */
    public function createNivel(array $data): Nivel
    {
        // Mapeo: Si el frontend envía 'nombre', lo usamos para 'nombre_nivel' en la BD.
        if (isset($data['nombre'])) {
            $data['nombre_nivel'] = $data['nombre'];
            unset($data['nombre']);
        }

        return Nivel::create($data);
    }

    /**
     * Busca un nivel por su ID.
     *
     * @param int $id
     * @return Nivel|null
     */
    public function findById(int $id) : ?Nivel
    {
        return Nivel::find($id);
    }
}
