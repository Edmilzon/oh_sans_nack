<?php

namespace App\Repositories;

use App\Model\AreaNivel;
use App\Model\Area;
use App\Model\Nivel;
use Illuminate\Database\Eloquent\Collection;

class AreaNivelRepository
{
    public function getAllAreasNiveles(): Collection
    {
        return AreaNivel::with(['area', 'nivel', 'olimpiada'])->get();
    }

    public function getByArea(int $id_area, ?int $idOlimpiada = null): Collection
    {
        $query = AreaNivel::where('id_area', $id_area);
        
        if ($idOlimpiada) {
            $query->where('id_olimpiada', $idOlimpiada);
        }
        
        return $query->get();
    }

    public function getByAreaAll(int $id_area, ?int $idOlimpiada = null): Collection
    {
        $query = AreaNivel::with([
            'area:id_area,nombre',
            'nivel:id_nivel,nombre', 
            'olimpiada:id_olimpiada,gestion'
        ])
        ->where('id_area', $id_area);

        if ($idOlimpiada) {
            $query->where('id_olimpiada', $idOlimpiada);
        }
        return $query->get();
    }

    public function getAreaNivelAsignadosAll(int $idOlimpiada): Collection
    {
        return Area::with([
            'areaNiveles' => function($query) use ($idOlimpiada) {
                $query->where('id_olimpiada', $idOlimpiada);
            },
            'areaNiveles.nivel:id_nivel,nombre'
        ])
        ->get(['id_area', 'nombre']);
    }
    
    public function getById(int $id): ?AreaNivel
    {
        return AreaNivel::with(['area', 'nivel', 'olimpiada'])->find($id);
    }

    public function update(int $id, array $data): bool
    {
        $areaNivel = AreaNivel::find($id);
        
        if (!$areaNivel) {
            return false;
        }

        return $areaNivel->update($data);
    }

    public function delete(int $id): bool
    {
        $areaNivel = AreaNivel::find($id);
        
        if (!$areaNivel) {
            return false;
        }

        return $areaNivel->delete();
    }
    
     public function getByAreaAndNivel(int $id_area, int $id_nivel, int $id_olimpiada): ?AreaNivel
    {
        return AreaNivel::where('id_area', $id_area)
            ->where('id_nivel', $id_nivel)
            ->where('id_olimpiada', $id_olimpiada)
            ->first();
    }

    public function createAreaNivel(array $data): AreaNivel
    {
        return AreaNivel::create($data);
    }

    public function getAreasConNivelesSimplificado(int $idOlimpiada): Collection
{
    return Area::with([
        'areaNiveles' => function($query) use ($idOlimpiada) {
            $query->where('id_olimpiada', $idOlimpiada)
                  ->where('activo', true);
        },
        'areaNiveles.nivel:id_nivel,nombre'
    ])
    ->whereHas('areaNiveles', function($query) use ($idOlimpiada) {
        $query->where('id_olimpiada', $idOlimpiada)
              ->where('activo', true);
    })
    ->get(['id_area', 'nombre']);
}

    public function getActualesByOlimpiada(int $idOlimpiada): Collection
    {
        return Area::with([
            'areaNiveles' => function($query) use ($idOlimpiada) {
                $query->where('id_olimpiada', $idOlimpiada)
                      ->where('activo', true);
            },
            'areaNiveles.nivel:id_nivel,nombre'
        ])
        ->whereHas('areaNiveles', function($query) use ($idOlimpiada) {
            $query->where('id_olimpiada', $idOlimpiada)
                  ->where('activo', true);
        })
        ->get(['id_area', 'nombre']);
    }

}