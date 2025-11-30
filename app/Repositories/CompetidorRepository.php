<?php

namespace App\Repositories;

use App\Model\Competidor;
use App\Model\Persona;
use App\Model\AreaOlimpiada;
use App\Model\AreaNivel;

class CompetidorRepository
{
    public function findWithRelations($id) {
        return Competidor::with(['persona', 'institucion', 'areaNivel', 'archivoCsv'])->find($id);
    }
    
    public function createPersona(array $data): Persona
    {
        return Persona::create($data);
    }

    public function createCompetidor(array $data): Competidor
    {
        return Competidor::create($data);
    }

    public function getAllCompetidores()
    {
        return Competidor::with(['persona', 'institucion', 'areaNivel.area', 'areaNivel.nivel', 'archivoCsv'])
            ->get();
    }

    public function findPersonasDuplicadas(string $ci, string $email, ?string $telefono = null)
    {
        return Persona::where(function($query) use ($ci, $email, $telefono) {
                $query->where('ci', $ci)
                      ->orWhere('email', $email);
                
                if ($telefono) {
                    $query->orWhere('telefono', $telefono);
                }
            })
            ->with(['competidor.archivoCsv.olimpiada'])
            ->get();
    }

    public function existePersona(string $ci, string $email, ?string $telefono = null): bool
    {
        return Persona::where('ci', $ci)
            ->orWhere('email', $email)
            ->orWhere('telefono', $telefono)
            ->exists();
    }

    public function getCompetidoresByArchivoCsv($archivoCsvId)
    {
        return Competidor::with(['persona', 'institucion', 'areaNivel'])
            ->where('id_archivo_csv', $archivoCsvId)
            ->get();
    }

    public function findAreaOlimpiada($areaId, $olimpiadaId)
    {
        return AreaOlimpiada::where('id_area', $areaId)
            ->where('id_olimpiada', $olimpiadaId)
            ->first();
    }

    public function findAreaNivel($areaId, $nivelId, $olimpiadaId)
    {
        return AreaNivel::where('id_area', $areaId)
            ->where('id_nivel', $nivelId)
            ->where('id_olimpiada', $olimpiadaId)
            ->first();
    }
}