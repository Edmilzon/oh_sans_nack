<?php

namespace App\Repositories;

use App\Model\Nivel;

class NivelRepository {

    public function getAllNivel(){
        return Nivel::all();
    }

    public function createNivel(array $data){
        return Nivel::create($data);
    }

    public function findById(int $id) : ?Nivel {
        return Nivel::find($id);
    }
}
