<?php

namespace App\Services;

use App\Repositories\CompetidorRepository;
use App\Model\Institucion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;
use Exception;

class CompetidorService
{
    protected $competidorRepository;

    public function __construct(CompetidorRepository $competidorRepository)
    {
        $this->competidorRepository = $competidorRepository;
    }

    public function procesarImportacion(array $competidoresData, int $olimpiadaId, int $archivoCsvId): array
    {
        // Aumentar tiempo y memoria para 3000+ registros
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $registrados = [];
        $duplicados = [];
        $errores = [];

        // =========================================================
        // FASE 1: PREPARACIÓN MASIVA (Batch Loading)
        // =========================================================

        // 1.1 Recolectar claves únicas del CSV
        $cis = [];
        $nombresInstituciones = [];

        foreach ($competidoresData as $item) {
            $cis[] = $item['persona']['ci'];
            $nombresInstituciones[] = Str::upper($item['institucion']['nombre']);
        }
        $nombresInstituciones = array_unique($nombresInstituciones);

        // 1.2 Cargar Catálogos a Memoria (Mapas asociativos por Nombre en Mayúscula)
        // Esto evita miles de consultas individuales "SELECT * FROM departamento WHERE nombre = ..."
        $deptosMap = $this->competidorRepository->getAllDepartamentos()->keyBy(fn($i) => Str::upper($i->nombre));
        $gradosMap = $this->competidorRepository->getAllGrados()->keyBy(fn($i) => Str::upper($i->nombre));
        $areasMap = $this->competidorRepository->getAllAreas()->keyBy(fn($i) => Str::upper($i->nombre));
        $nivelesMap = $this->competidorRepository->getAllNiveles()->keyBy(fn($i) => Str::upper($i->nombre));

        // 1.3 Cargar Estructura de la Olimpiada (Áreas y Niveles habilitados)
        $areaOlimpiadas = $this->competidorRepository->getAreaOlimpiadas($olimpiadaId);
        $areaOlimpiadaMap = $areaOlimpiadas->keyBy('id_area'); // Mapa [id_area => AreaOlimpiada]

        // Lista plana de AreaNiveles disponibles en esta olimpiada
        $areaNivelesDisponibles = $this->competidorRepository->getAreaNiveles($areaOlimpiadas->pluck('id_area_olimpiada')->toArray());

        // 1.4 Cargar Personas ya existentes (con sus inscripciones previas)
        $personasExistentes = $this->competidorRepository->getPersonasConCompetidores($cis);
        $mapaPersonas = $personasExistentes->keyBy('ci');

        // 1.5 Gestión de Instituciones (Crear las que faltan en bloque)
        $institucionesExistentes = $this->competidorRepository->getInstitucionesByNombres($nombresInstituciones);
        $institucionesMap = $institucionesExistentes->keyBy(fn($i) => Str::upper($i->nombre));

        foreach ($nombresInstituciones as $nombreInst) {
            if (!$institucionesMap->has($nombreInst)) {
                // REGLA: Si institución no existe, se crea.
                $nuevaInst = Institucion::create(['nombre' => $nombreInst]);
                $institucionesMap->put($nombreInst, $nuevaInst);
            }
        }

        // =========================================================
        // FASE 2: PROCESAMIENTO TRANSACCIONAL
        // =========================================================

        DB::beginTransaction();

        try {
            $linea = 0;
            foreach ($competidoresData as $item) {
                $linea++;
                try {
                    $personaData = $item['persona'];
                    $compData = $item['competidor'];

                    // A. Validación Estricta de Catálogos (BD vs CSV)
                    $deptoNombre = Str::upper($compData['departamento']);
                    $gradoNombre = Str::upper($compData['grado_escolar']);
                    $areaNombre = Str::upper($item['area']['nombre']);
                    $nivelNombre = Str::upper($item['nivel']['nombre']);

                    if (!$deptosMap->has($deptoNombre)) throw new Exception("Departamento '$deptoNombre' no existe.");
                    if (!$gradosMap->has($gradoNombre)) throw new Exception("Grado '$gradoNombre' no existe.");
                    if (!$areasMap->has($areaNombre)) throw new Exception("Área '$areaNombre' no existe.");
                    if (!$nivelesMap->has($nivelNombre)) throw new Exception("Nivel '$nivelNombre' no existe.");

                    // Objetos resueltos desde memoria
                    $departamento = $deptosMap->get($deptoNombre);
                    $grado = $gradosMap->get($gradoNombre);
                    $area = $areasMap->get($areaNombre);
                    $nivel = $nivelesMap->get($nivelNombre);
                    $institucion = $institucionesMap->get(Str::upper($item['institucion']['nombre']));

                    // B. Resolver AreaNivel (La "Clase" específica)
                    if (!$areaOlimpiadaMap->has($area->id_area)) {
                        throw new Exception("El área '{$area->nombre}' no está habilitada en esta gestión.");
                    }
                    $areaOlimpiada = $areaOlimpiadaMap->get($area->id_area);

                    // Buscamos en la colección en memoria
                    $areaNivel = $areaNivelesDisponibles->first(function($an) use ($areaOlimpiada, $nivel) {
                        return $an->id_area_olimpiada == $areaOlimpiada->id_area_olimpiada && $an->id_nivel == $nivel->id_nivel;
                    });

                    if (!$areaNivel) {
                        throw new Exception("La combinación Área '{$area->nombre}' - Nivel '{$nivel->nombre}' no está configurada.");
                    }

                    // C. Lógica de Negocio: Registro vs Asignación vs Duplicado
                    $persona = $mapaPersonas->get($personaData['ci']);
                    $tipoAccion = '';
                    $idPersonaUsar = null;

                    if ($persona) {
                        // C.1 La persona existe. ¿Ya tiene esta materia?
                        $yaInscrito = $persona->competidores->first(function($comp) use ($areaNivel) {
                            return $comp->id_area_nivel == $areaNivel->id_area_nivel;
                        });

                        if ($yaInscrito) {
                            // CASO DUPLICADO: Mismo estudiante, misma materia.
                            $origen = $yaInscrito->archivoCsv->nombre ?? 'Registro manual previo';
                            $item['origen_duplicado'] = $origen;
                            $duplicados[] = $item;
                            continue; // Saltamos este registro
                        }

                        // CASO ASIGNACIÓN: Estudiante conocido, materia nueva.
                        $idPersonaUsar = $persona->id_persona;
                        $tipoAccion = 'ASIGNADO';

                    } else {
                        // CASO REGISTRO: Estudiante nuevo.
                        $nuevaPersona = $this->competidorRepository->createPersona($personaData);

                        // Actualizamos mapa local para detectar duplicados dentro del mismo CSV actual
                        $nuevaPersona->setRelation('competidores', collect([]));
                        $mapaPersonas->put($nuevaPersona->ci, $nuevaPersona);

                        $idPersonaUsar = $nuevaPersona->id_persona;
                        $persona = $nuevaPersona;
                        $tipoAccion = 'REGISTRADO';
                    }

                    // D. Persistencia (Crear Competidor)
                    $nuevoCompetidor = $this->competidorRepository->createCompetidor([
                        'id_persona' => $idPersonaUsar,
                        'id_institucion' => $institucion->id_institucion,
                        'id_departamento' => $departamento->id_departamento,
                        'id_area_nivel' => $areaNivel->id_area_nivel,
                        'id_grado_escolaridad' => $grado->id_grado_escolaridad,
                        'id_archivo_csv' => $archivoCsvId,
                        'contacto_tutor' => $compData['contacto_tutor'] ?? null,
                        'genero' => $personaData['genero'],
                        'estado_evaluacion' => 'disponible',
                    ]);

                    // Actualizar relaciones en memoria para validaciones futuras en este loop
                    // Mockeamos la relación para que el reporte funcione
                    $nuevoCompetidor->setRelation('archivoCsv', (object)['nombre' => 'Este Archivo Actual']);
                    $persona->competidores->push($nuevoCompetidor);

                    $registrados[] = [
                        'persona' => $persona,
                        'tipo' => $tipoAccion,
                        'area' => $area->nombre,
                        'nivel' => $nivel->nombre,
                        'institucion' => $institucion->nombre
                    ];

                } catch (Throwable $e) {
                    $item['error_message'] = $e->getMessage();
                    $item['linea'] = $linea;
                    $errores[] = $item;
                }
            }

            DB::commit(); // Confirmar bloque de 3000 insertos

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
}
