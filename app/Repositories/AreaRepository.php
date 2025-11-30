<?php

namespace App\Repositories;

use App\Model\Area;

class AreaRepository{

    public function getAllAreas(){
        return Area::all();
    }

    public function createArea(array $data){
        // Si el frontend envía 'nombre', lo asignamos a 'nombre_area'
        if (isset($data['nombre'])) {
            $data['nombre_area'] = $data['nombre'];
            unset($data['nombre']);
        }
        return Area::create($data);
    }

    public function getAreasByGestion(string $gestion)
    {
        return Area::whereHas('areaOlimpiadas.olimpiada', function ($query) use ($gestion) {
            $query->where('gestion_olimp', $gestion);
        })
        ->select('id_area', 'nombre_area as nombre')
        ->get();
    }
}
