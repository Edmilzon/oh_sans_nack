<?php

namespace App\Repositories;

use App\Model\Competidor;
use App\Model\Persona;
use App\Model\Institucion;
use App\Model\Departamento;
use App\Model\GradoEscolaridad;
use App\Model\Area;
use App\Model\Nivel;
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

    /**
     * Busca Personas existentes masivamente y CARGA sus inscripciones previas.
     * Esto permite detectar si ya están inscritos en una materia específica.
     */
    public function getPersonasConCompetidores(array $cis): Collection
    {
        return Persona::whereIn('ci', $cis)
            // Cargamos competidores para ver sus materias (areaNivel) y el archivo de origen
            ->with(['competidores.archivoCsv', 'competidores.areaNivel'])
            ->get();
    }

    /**
     * Busca Instituciones por nombres masivamente.
     */
    public function getInstitucionesByNombres(array $nombres): Collection
    {
        return Institucion::whereIn('nombre', $nombres)->get();
    }

    // --- CARGA DE CATÁLOGOS COMPLETOS (Para cache en memoria) ---
    public function getAllDepartamentos(): Collection { return Departamento::all(); }
    public function getAllGrados(): Collection { return GradoEscolaridad::all(); }
    public function getAllAreas(): Collection { return Area::all(); }
    public function getAllNiveles(): Collection { return Nivel::all(); }

    // --- ESTRUCTURA OLIMPIADA ---
    public function getAreaOlimpiadas(int $olimpiadaId): Collection
    {
        return AreaOlimpiada::where('id_olimpiada', $olimpiadaId)->get();
    }

    public function getAreaNiveles(array $areaOlimpiadaIds): Collection
    {
        return AreaNivel::whereIn('id_area_olimpiada', $areaOlimpiadaIds)
            ->with(['areaOlimpiada', 'nivel']) // Eager loading para macheo rápido
            ->get();
    }
}
