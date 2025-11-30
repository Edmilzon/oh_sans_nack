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
        // Se cargan las relaciones anidadas: Area y Olimpiada ahora están dentro de AreaOlimpiada
        return AreaNivel::with(['areaOlimpiada.area', 'nivel', 'areaOlimpiada.olimpiada'])->get();
    }

    public function getByArea(int $id_area, ?int $idOlimpiada = null): Collection
    {
        // Filtramos usando la relación padre areaOlimpiada
        $query = AreaNivel::whereHas('areaOlimpiada', function ($q) use ($id_area, $idOlimpiada) {
            $q->where('id_area', $id_area);

            if ($idOlimpiada) {
                $q->where('id_olimpiada', $idOlimpiada);
            }
        });

        return $query->get();
    }

    public function getByAreaAll(int $id_area, ?int $idOlimpiada = null): Collection
    {
        $query = AreaNivel::with([
            // Alias para compatibilidad con Frontend: nombre_area -> nombre, nombre_nivel -> nombre
            'areaOlimpiada.area:id_area,nombre_area as nombre',
            'nivel:id_nivel,nombre_nivel as nombre',
            'areaOlimpiada.olimpiada:id_olimpiada,gestion_olimp as gestion'
        ])
        ->whereHas('areaOlimpiada', function ($q) use ($id_area, $idOlimpiada) {
            $q->where('id_area', $id_area);

            if ($idOlimpiada) {
                $q->where('id_olimpiada', $idOlimpiada);
            }
        });

        return $query->get();
    }

    public function getAreaNivelAsignadosAll(int $idOlimpiada): Collection
    {
        // Busca Areas que tengan AreaNiveles configurados para la olimpiada específica
        return Area::whereHas('areaOlimpiadas', function($query) use ($idOlimpiada) {
            $query->where('id_olimpiada', $idOlimpiada)
                  ->whereHas('areaNiveles');
        })
        ->with([
            'areaOlimpiadas' => function($query) use ($idOlimpiada) {
                $query->where('id_olimpiada', $idOlimpiada);
            },
            // Alias en la relación anidada
            'areaOlimpiadas.areaNiveles.nivel:id_nivel,nombre_nivel as nombre'
        ])
        ->get(['id_area', 'nombre_area as nombre']);
    }

    public function getById(int $id): ?AreaNivel
    {
        return AreaNivel::with(['areaOlimpiada.area', 'nivel', 'areaOlimpiada.olimpiada'])->find($id);
    }

    public function update(int $id, array $data): bool
    {
        $areaNivel = AreaNivel::find($id);

        if (!$areaNivel) {
            return false;
        }

        // Mapeo del campo 'activo' (Front) a 'es_activo_area_nivel' (BD)
        if (isset($data['activo'])) {
            $data['es_activo_area_nivel'] = $data['activo'];
            unset($data['activo']);
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
        // Búsqueda cruzada a través de la relación intermedia
        return AreaNivel::whereHas('areaOlimpiada', function ($q) use ($id_area, $id_olimpiada) {
            $q->where('id_area', $id_area)
              ->where('id_olimpiada', $id_olimpiada);
        })
        ->where('id_nivel', $id_nivel)
        ->first();
    }

    public function createAreaNivel(array $data): AreaNivel
    {
        // Mapeo de entrada para creación
        if (isset($data['activo'])) {
            $data['es_activo_area_nivel'] = $data['activo'];
            unset($data['activo']);
        }
        return AreaNivel::create($data);
    }

    public function getAreasConNivelesSimplificado(int $idOlimpiada): Collection
    {
        return Area::with([
            'areaOlimpiadas' => function($query) use ($idOlimpiada) {
                $query->where('id_olimpiada', $idOlimpiada);
            },
            'areaOlimpiadas.areaNiveles' => function($query) {
                $query->where('es_activo_area_nivel', true);
            },
            'areaOlimpiadas.areaNiveles.nivel:id_nivel,nombre_nivel as nombre'
        ])
        ->whereHas('areaOlimpiadas', function($query) use ($idOlimpiada) {
            $query->where('id_olimpiada', $idOlimpiada)
                  ->whereHas('areaNiveles', function($q) {
                      $q->where('es_activo_area_nivel', true);
                  });
        })
        ->get(['id_area', 'nombre_area as nombre']);
    }

    public function getActualesByOlimpiada(int $idOlimpiada): Collection
    {
        return $this->getAreasConNivelesSimplificado($idOlimpiada);
    }
}
