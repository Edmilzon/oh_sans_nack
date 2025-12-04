<?php

namespace App\Repositories;

use App\Model\Area;

class AreaRepository{

    public function getAllAreas(){
        return Area::all();
    }

        public function createArea(array $data){

            return Area::create($data);

        }

        public function getAreasByGestion(string $gestion)

        {

            return Area::whereHas('olimpiadas', function ($query) use ($gestion) {

                $query->where('gestion', $gestion);

            })->select('id_area', 'nombre')->get();

        }

    }

    