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

    public function getNivelList(){
        return $this->nivelRepository->getAllNivel();
    }

    public function createNewNivel(array $data){
        return $this->nivelRepository->createNivel($data);
    }

    public function findById(int $id) : ?Nivel {
        return $this->nivelRepository->findById($id);
    }
}
