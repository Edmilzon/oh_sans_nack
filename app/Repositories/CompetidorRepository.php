<?php

namespace App\Repositories;

use App\Model\Competidor;
use App\Model\Persona;
use App\Model\Institucion;
use App\Model\Departamento;
use App\Model\GradoEscolaridad;
use App\Model\Area;
use App\Model\Nivel;
use App\Model\DescalificacionAdministrativa;
use App\Model\AreaOlimpiada;
use App\Model\AreaNivel;
use Illuminate\Database\Eloquent\Collection;

class CompetidorRepository
{
    public function createPersona(array $data): Persona
    {
        return Persona::create($data);
    }

    public function createCompetidor(array $data): Competidor
    {
        return Competidor::create($data);
    }

    public function getPersonasConCompetidores(array $cis): Collection
    {
        return Persona::whereIn('ci', $cis)

            ->with(['competidores.archivoCsv', 'competidores.areaNivel'])
            ->get();
    }

    public function getInstitucionesByNombres(array $nombres): Collection
    {
        return Institucion::whereIn('nombre', $nombres)->get();
    }

    public function getAllDepartamentos(): Collection { return Departamento::all(); }
    public function getAllGrados(): Collection { return GradoEscolaridad::all(); }
    public function getAllAreas(): Collection { return Area::all(); }
    public function getAllNiveles(): Collection { return Nivel::all(); }

    public function getAreaOlimpiadas(int $olimpiadaId): Collection
    {
        return AreaOlimpiada::where('id_olimpiada', $olimpiadaId)->get();
    }

    public function getAreaNiveles(array $areaOlimpiadaIds): Collection
    {
        return AreaNivel::whereIn('id_area_olimpiada', $areaOlimpiadaIds)
            ->with(['areaOlimpiada', 'nivel'])
            ->get();
    }

    public function registrarDescalificacionAdministrativa(int $id_competidor, string $observaciones): void
    {
        DescalificacionAdministrativa::create([
            'id_competidor' => $id_competidor,
            'observaciones' => $observaciones,
        ]);
    }
}
