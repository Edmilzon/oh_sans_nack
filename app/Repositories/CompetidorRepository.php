<?php

namespace App\Repositories;

use App\Model\Competidor;
use App\Model\Persona;
use App\Model\AreaOlimpiada;
use App\Model\AreaNivel;

class CompetidorRepository
{
    public function findWithRelations($id) {
        // Rutas actualizadas para eager loading
        return Competidor::with([
            'persona',
            'institucion',
            'inscripciones.areaNivel.areaOlimpiada.area', // Acceso a traves de inscripciones
            'inscripciones.areaNivel.nivel',
            'archivoCsv'
        ])->find($id);
    }

    public function createPersona(array $data): Persona
    {
        // Mapeo seguro de campos del request a columnas de BD con sufijo _pers
        $mappedData = [
            'nombre_pers' => $data['nombre'] ?? ($data['nombre_pers'] ?? null),
            'apellido_pers' => $data['apellido'] ?? ($data['apellido_pers'] ?? null),
            'ci_pers' => $data['ci'] ?? ($data['ci_pers'] ?? null),
            'email_pers' => $data['email'] ?? ($data['email_pers'] ?? null),
            'telefono_pers' => $data['telefono'] ?? ($data['telefono_pers'] ?? null),
        ];

        // Filtramos nulos para dejar que la BD use defaults si aplica o lance error limpio
        $mappedData = array_filter($mappedData, fn($value) => !is_null($value));

        return Persona::create($mappedData);
    }

    public function createCompetidor(array $data): Competidor
    {
        return Competidor::create($data);
    }

    public function getAllCompetidores()
    {
        return Competidor::with([
            'persona',
            'institucion',
            // Cargar relaciones profundas para obtener nombres de area y nivel si se necesitan en la lista
            // Nota: Competidor tiene muchas inscripciones, usualmente se carga 'inscripciones'
            'inscripciones.areaNivel.areaOlimpiada.area',
            'inscripciones.areaNivel.nivel',
            'archivoCsv'
        ])->get();
    }

    public function findPersonasDuplicadas(string $ci, string $email, ?string $telefono = null)
    {
        return Persona::where(function($query) use ($ci, $email, $telefono) {
                $query->where('ci_pers', $ci)
                      ->orWhere('email_pers', $email);

                if ($telefono) {
                    $query->orWhere('telefono_pers', $telefono);
                }
            })
            ->with(['competidor.archivoCsv']) // Asumiendo relación en modelo Competidor
            ->get();
    }

    public function existePersona(string $ci, string $email, ?string $telefono = null): bool
    {
        return Persona::where('ci_pers', $ci)
            ->orWhere('email_pers', $email)
            ->when($telefono, function($q) use ($telefono) {
                return $q->orWhere('telefono_pers', $telefono);
            })
            ->exists();
    }

    public function getCompetidoresByArchivoCsv($archivoCsvId)
    {
        return Competidor::with(['persona', 'institucion', 'inscripciones.areaNivel'])
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
        // Buscar AreaNivel navegando por AreaOlimpiada
        return AreaNivel::whereHas('areaOlimpiada', function($q) use ($areaId, $olimpiadaId) {
            $q->where('id_area', $areaId)
              ->where('id_olimpiada', $olimpiadaId);
        })
        ->where('id_nivel', $nivelId)
        ->first();
    }
}
