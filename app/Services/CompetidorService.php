<?php

namespace App\Services;

use App\Repositories\CompetidorRepository;
use App\Model\Institucion;
use App\Model\Grupo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;
use Exception;
use App\Model\Competidor;

class CompetidorService
{
    protected $competidorRepository;

    public function __construct(CompetidorRepository $competidorRepository)
    {
        $this->competidorRepository = $competidorRepository;
    }

    private function normalizarTexto(?string $texto): string
    {
        if (is_null($texto)) return '';
        return Str::upper(Str::ascii(trim($texto)));
    }

    public function procesarImportacion(array $competidoresData, int $olimpiadaId, int $archivoCsvId): array
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $registrados = [];
        $duplicados = [];
        $errores = [];

        $cis = [];
        $nombresInstitucionesOriginales = [];
        $nombresGruposOriginales = [];

        foreach ($competidoresData as $item) {
            $cis[] = $item['persona']['ci'];

            $instNombre = $item['institucion']['nombre'] ?? '';
            $instKey = $this->normalizarTexto($instNombre);
            if ($instKey) {
                $nombresInstitucionesOriginales[$instKey] = trim($instNombre);
            }

            $grupoNombre = $item['competidor']['grupo'] ?? null;
            if ($grupoNombre) {
                $grupoKey = $this->normalizarTexto($grupoNombre);
                $nombresGruposOriginales[$grupoKey] = trim($grupoNombre);
            }
        }

        $deptosMap = $this->competidorRepository->getAllDepartamentos()
            ->keyBy(fn($i) => $this->normalizarTexto($i->nombre));

        $gradosMap = $this->competidorRepository->getAllGrados()
            ->keyBy(fn($i) => $this->normalizarTexto($i->nombre));

        $areasMap = $this->competidorRepository->getAllAreas()
            ->keyBy(fn($i) => $this->normalizarTexto($i->nombre));

        $nivelesMap = $this->competidorRepository->getAllNiveles()
            ->keyBy(fn($i) => $this->normalizarTexto($i->nombre));

        $institucionesExistentes = $this->competidorRepository->getInstitucionesByNombres(array_values($nombresInstitucionesOriginales));

        $institucionesMap = $institucionesExistentes->keyBy(fn($i) => $this->normalizarTexto($i->nombre));

        foreach ($nombresInstitucionesOriginales as $keyNormalizada => $nombreOriginal) {
            if (!$institucionesMap->has($keyNormalizada)) {
                $nuevaInst = Institucion::create(['nombre' => $nombreOriginal]);
                $institucionesMap->put($keyNormalizada, $nuevaInst);
            }
        }

        $gruposExistentes = Grupo::whereIn('nombre', array_values($nombresGruposOriginales))->get();
        $gruposMap = $gruposExistentes->keyBy(fn($g) => $this->normalizarTexto($g->nombre));

        foreach ($nombresGruposOriginales as $keyNormalizada => $nombreOriginal) {
            if (!$gruposMap->has($keyNormalizada)) {
                $nuevoGrupo = Grupo::create(['nombre' => $nombreOriginal]);
                $gruposMap->put($keyNormalizada, $nuevoGrupo);
            }
        }

        $areaOlimpiadas = $this->competidorRepository->getAreaOlimpiadas($olimpiadaId);
        $areaOlimpiadaMap = $areaOlimpiadas->keyBy('id_area');

        $areaNivelesDisponibles = $this->competidorRepository->getAreaNiveles($areaOlimpiadas->pluck('id_area_olimpiada')->toArray());

        $personasExistentes = $this->competidorRepository->getPersonasConCompetidores($cis);
        $mapaPersonas = $personasExistentes->keyBy('ci');

        DB::beginTransaction();

        try {
            $linea = 0;
            foreach ($competidoresData as $item) {
                $linea++;
                try {
                    $personaData = $item['persona'];
                    $compData = $item['competidor'];
                    $deptoNombre = $this->normalizarTexto($compData['departamento']);
                    $gradoNombre = $this->normalizarTexto($compData['grado_escolar']);
                    $areaNombre = $this->normalizarTexto($item['area']['nombre']);
                    $nivelNombre = $this->normalizarTexto($item['nivel']['nombre']);
                    $instNombre = $this->normalizarTexto($item['institucion']['nombre']);
                    $grupoNombre = isset($compData['grupo']) ? $this->normalizarTexto($compData['grupo']) : null;
                    if (!$deptosMap->has($deptoNombre)) throw new Exception("Departamento '{$compData['departamento']}' no existe.");
                    if (!$gradosMap->has($gradoNombre)) throw new Exception("Grado '{$compData['grado_escolar']}' no existe.");
                    if (!$areasMap->has($areaNombre)) throw new Exception("Área '{$item['area']['nombre']}' no existe.");
                    if (!$nivelesMap->has($nivelNombre)) throw new Exception("Nivel '{$item['nivel']['nombre']}' no existe.");

                    $departamento = $deptosMap->get($deptoNombre);
                    $grado = $gradosMap->get($gradoNombre);
                    $area = $areasMap->get($areaNombre);
                    $nivel = $nivelesMap->get($nivelNombre);
                    $institucion = $institucionesMap->get($instNombre);

                    $idGrupo = null;
                    if ($grupoNombre && $gruposMap->has($grupoNombre)) {
                        $idGrupo = $gruposMap->get($grupoNombre)->id_grupo;
                    }
                    if (!$areaOlimpiadaMap->has($area->id_area)) {
                        throw new Exception("El área '{$area->nombre}' no está habilitada en esta gestión.");
                    }
                    $areaOlimpiada = $areaOlimpiadaMap->get($area->id_area);

                    $areaNivel = $areaNivelesDisponibles->first(function($an) use ($areaOlimpiada, $nivel) {
                        return $an->id_area_olimpiada == $areaOlimpiada->id_area_olimpiada && $an->id_nivel == $nivel->id_nivel;
                    });

                    if (!$areaNivel) {
                        throw new Exception("La combinación Área '{$area->nombre}' - Nivel '{$nivel->nombre}' no está configurada.");
                    }

                    $persona = $mapaPersonas->get($personaData['ci']);
                    $tipoAccion = '';
                    $idPersonaUsar = null;

                    if ($persona) {
                        $yaInscrito = $persona->competidores->first(function($comp) use ($areaNivel) {
                            return $comp->id_area_nivel == $areaNivel->id_area_nivel;
                        });

                        if ($yaInscrito) {
                            $origen = $yaInscrito->archivoCsv->nombre ?? 'Registro previo';
                            $item['origen_duplicado'] = $origen;
                            $duplicados[] = $item;
                            continue;
                        }

                        $idPersonaUsar = $persona->id_persona;
                        $tipoAccion = 'ASIGNADO';
                    } else {
                        $nuevaPersona = $this->competidorRepository->createPersona($personaData);
                        $nuevaPersona->setRelation('competidores', collect([]));
                        $mapaPersonas->put($nuevaPersona->ci, $nuevaPersona);

                        $idPersonaUsar = $nuevaPersona->id_persona;
                        $persona = $nuevaPersona;
                        $tipoAccion = 'REGISTRADO';
                    }

                    $nuevoCompetidor = $this->competidorRepository->createCompetidor([
                        'id_persona' => $idPersonaUsar,
                        'id_institucion' => $institucion->id_institucion,
                        'id_departamento' => $departamento->id_departamento,
                        'id_area_nivel' => $areaNivel->id_area_nivel,
                        'id_grado_escolaridad' => $grado->id_grado_escolaridad,
                        'id_grupo' => $idGrupo,
                        'id_archivo_csv' => $archivoCsvId,
                        'contacto_tutor' => $compData['contacto_tutor'] ?? null,
                        'genero' => $personaData['genero'],
                        'estado_evaluacion' => 'disponible',
                    ]);

                    $nuevoCompetidor->setRelation('archivoCsv', (object)['nombre' => 'Este Archivo']);
                    $persona->competidores->push($nuevoCompetidor);

                    $registrados[] = [
                        'persona' => $persona,
                        'tipo' => $tipoAccion,
                        'area' => $area->nombre,
                        'nivel' => $nivel->nombre,
                        'institucion' => $institucion->nombre
                    ];

                    if ($linea % 500 === 0) {
                        DB::commit();
                        DB::beginTransaction();
                    }

                } catch (Throwable $e) {
                    $item['error_message'] = $e->getMessage();
                    $item['linea'] = $linea;
                    $errores[] = $item;
                }
            }

            DB::commit();

        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'registrados' => $registrados,
            'duplicados' => $duplicados,
            'errores' => $errores,
        ];
    }

    public function descalificarCompetidor(int $id_competidor, string $observaciones): Competidor
    {
        $competidor = Competidor::findOrFail($id_competidor);
 
        $this->competidorRepository->registrarDescalificacionAdministrativa($id_competidor, $observaciones);
 
        return $competidor;
    }
}
