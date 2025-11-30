<?php

namespace App\Services;

use App\Repositories\NivelRepository;
use App\Model\Nivel;
use Illuminate\Database\Eloquent\Collection;

class NivelService {
    protected $nivelRepository;

    public function __construct(NivelRepository $nivelRepository){
        $this->nivelRepository = $nivelRepository;
    }

    /**
     * Obtiene el listado de todos los niveles.
     * @return Collection
     */
    public function getNivelList(): Collection
    {
        return $this->nivelRepository->getAllNivel();
    }

    /**
     * Crea un nuevo nivel.
     * @param array $data Contiene el campo 'nombre'.
     * @return Nivel
     */
    public function createNewNivel(array $data): Nivel
    {
        // El Repositorio se encarga de mapear 'nombre' a 'nombre_nivel'
        return $this->nivelRepository->createNivel($data);
    }

    /**
     * Busca un nivel por su ID.
     * @param int $id
     * @return Nivel|null
     */
    public function findById(int $id) : ?Nivel
    {
        return $this->nivelRepository->findById($id);
    }
}
