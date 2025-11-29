<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportarRequest;
use App\Repositories\CompetidorRepository;
use App\Services\CompetidorService;
use App\Model\Institucion;
use App\Model\Grupo;
use App\Model\GrupoCompetidor;
use App\Model\Area;
use App\Model\Nivel;
use App\Model\AreaNivel;
use App\Model\Competidor;
use App\Model\Persona;
use App\Model\ArchivoCsv;
use App\Model\Olimpiada;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ImportarcsvController extends Controller
{
    protected $competidorService;
    protected $competidorRepository;

    public function __construct(
        CompetidorService $competidorService, 
        CompetidorRepository $competidorRepository
    ) {
        $this->competidorService = $competidorService;
        $this->competidorRepository = $competidorRepository;
    }

    public function importar(ImportarRequest $request, $gestion): JsonResponse
    {
        try {
            $olimpiada = Olimpiada::where('gestion', $gestion)->first();
            if (!$olimpiada) {
                return response()->json([
                    'success' => false,
                    'message' => "La olimpiada de gestión $gestion no existe."
                ], 404);
            }

            $nombreArchivo = $request->input('nombre_archivo');
            $archivoCsv = ArchivoCsv::firstOrCreate(
                ['nombre' => $nombreArchivo, 'id_olimpiada' => $olimpiada->id_olimpiada],
                ['fecha' => now()]
            );

            $competidoresCreados = [];
            $competidoresDuplicados = [];
            $competidoresConError = [];

            $competidoresData = $request->input('competidores');
            $lotes = array_chunk($competidoresData, 10);

            foreach ($lotes as $loteIndex => $lote) {
                DB::beginTransaction();

                try {
                    foreach ($lote as $index => $competidorData) {
                        $indiceGlobal = ($loteIndex * 10) + $index;
                        
                        try {
                            // 1. Validar que el área exista
                            $area = Area::where('nombre', $competidorData['area']['nombre'])->first();
                            if (!$area) {
                                $competidoresConError[] = [
                                    'indice' => $indiceGlobal,
                                    'nombre' => $competidorData['persona']['nombre'] . ' ' . $competidorData['persona']['apellido'],
                                    'error' => "El área '{$competidorData['area']['nombre']}' no existe en la base de datos"
                                ];
                                continue;
                            }

                            // 2. Validar que el nivel exista
                            $nivel = Nivel::where('nombre', $competidorData['nivel']['nombre'])->first();
                            if (!$nivel) {
                                $competidoresConError[] = [
                                    'indice' => $indiceGlobal,
                                    'nombre' => $competidorData['persona']['nombre'] . ' ' . $competidorData['persona']['apellido'],
                                    'error' => "El nivel '{$competidorData['nivel']['nombre']}' no existe en la base de datos"
                                ];
                                continue;
                            }

                            // 3. Validar que el área_nivel exista
                            $areaNivel = AreaNivel::where('id_area', $area->id_area)
                                ->where('id_nivel', $nivel->id_nivel)
                                ->where('id_olimpiada', $olimpiada->id_olimpiada)
                                ->first();

                            if (!$areaNivel) {
                                $competidoresConError[] = [
                                    'indice' => $indiceGlobal,
                                    'nombre' => $competidorData['persona']['nombre'] . ' ' . $competidorData['persona']['apellido'],
                                    'error' => "La combinación del área '{$area->nombre}' con el nivel '{$nivel->nombre}' no existe para la olimpiada de gestión $gestion"
                                ];
                                continue;
                            }

                            // 4. Verificar duplicados globales CI, email y telefono
                            $resultadoDuplicado = $this->verificarDuplicadoGlobal(
                                $competidorData['persona']['ci'],
                                $competidorData['persona']['email'],
                                $competidorData['persona']['telefono'] ?? null
                            );

                            if ($resultadoDuplicado['es_duplicado']) {
                                $personaDuplicada = $resultadoDuplicado['persona_existente'];
                                $competidorDuplicado = $personaDuplicada->competidor;
                                
                                $archivoDuplicado = $competidorDuplicado ? $competidorDuplicado->archivoCsv : null;
                                $olimpiadaDuplicada = $archivoDuplicado ? $archivoDuplicado->olimpiada : null;

                                $mensajeDuplicado = "Competidor '{$personaDuplicada->nombre} {$personaDuplicada->apellido}' duplicado";
                                
                                if ($archivoDuplicado && $olimpiadaDuplicada) {
                                    $mensajeDuplicado .= " en el archivo '{$archivoDuplicado->nombre}' de la gestión {$olimpiadaDuplicada->gestion}";
                                }

                                $competidoresDuplicados[] = [
                                    'indice' => $indiceGlobal,
                                    'nombre' => $competidorData['persona']['nombre'] . ' ' . $competidorData['persona']['apellido'],
                                    'ci' => $competidorData['persona']['ci'],
                                    'email' => $competidorData['persona']['email'],
                                    'telefono' => $competidorData['persona']['telefono'] ?? null,
                                    'area' => $area->nombre,
                                    'nivel' => $nivel->nombre,
                                    'campos_duplicados' => $resultadoDuplicado['campos_duplicados'],
                                    'motivo' => $mensajeDuplicado,
                                    'persona_existente' => $personaDuplicada,
                                    'archivo_duplicado' => $archivoDuplicado ? $archivoDuplicado->nombre : 'N/A',
                                    'gestion_duplicada' => $olimpiadaDuplicada ? $olimpiadaDuplicada->gestion : 'N/A'
                                ];
                                continue;
                            }

                            // 5. Buscar o crear Institución
                            $institucion = Institucion::firstOrCreate(
                                ['nombre' => $competidorData['institucion']['nombre']],
                            );

                            // 6. Preparar datos para el Service
                            $data = [
                                'nombre' => $competidorData['persona']['nombre'],
                                'apellido' => $competidorData['persona']['apellido'],
                                'ci' => $competidorData['persona']['ci'],
                                'genero' => $competidorData['persona']['genero'],
                                'telefono' => $competidorData['persona']['telefono'] ?? null,
                                'email' => $competidorData['persona']['email'],

                                'grado_escolar' => $competidorData['competidor']['grado_escolar'],
                                'departamento' => $competidorData['competidor']['departamento'],
                                'contacto_tutor' => $competidorData['competidor']['contacto_tutor'] ?? null,
                                
                                'id_institucion' => $institucion->id_institucion,
                                'id_area_nivel' => $areaNivel->id_area_nivel,
                                'id_archivo_csv' => $archivoCsv->id_archivo_csv,
                            ];

                            // 7. Crear competidor
                            $persona = $this->competidorService->createNewCompetidor($data);

                            $competidoresCreados[] = [
                                'indice' => $indiceGlobal,
                                'persona' => $persona,
                                'nombre_completo' => $persona->nombre . ' ' . $persona->apellido
                            ];

                        } catch (\Exception $e) {
                            $competidoresConError[] = [
                                'indice' => $indiceGlobal,
                                'nombre' => $competidorData['persona']['nombre'] . ' ' . $competidorData['persona']['apellido'],
                                'error' => $e->getMessage()
                            ];
                            continue;
                        }
                    }

                    DB::commit();

                } catch (\Exception $e) {
                    DB::rollBack();
                    
                    foreach ($lote as $index => $competidorData) {
                        $indiceGlobal = ($loteIndex * 10) + $index;
                        $competidoresConError[] = [
                            'indice' => $indiceGlobal,
                            'nombre' => $competidorData['persona']['nombre'] . ' ' . $competidorData['persona']['apellido'],
                            'error' => 'Error en procesamiento de lote: ' . $e->getMessage()
                        ];
                    }
                }
            }

            $response = $this->construirRespuestaImportacion(
                $competidoresCreados,
                $competidoresDuplicados,
                $competidoresConError,
                $archivoCsv,
                $gestion
            );

            return response()->json($response, 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Hubo un error al importar competidores',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function verificarDuplicadoGlobal(string $ci, string $email, ?string $telefono): array
    {
        $personasDuplicadas = $this->competidorRepository->findPersonasDuplicadas($ci, $email, $telefono);

        if ($personasDuplicadas->isEmpty()) {
            return [
                'es_duplicado' => false,
                'campos_duplicados' => [],
                'mensaje' => '',
                'persona_existente' => null
            ];
        }

        $personaDuplicada = $personasDuplicadas->first();
        $camposDuplicados = [];

        if ($personaDuplicada->ci === $ci) {
            $camposDuplicados[] = 'documento_identidad';
        }
        if ($personaDuplicada->email === $email) {
            $camposDuplicados[] = 'email';
        }
        if ($telefono && $personaDuplicada->telefono === $telefono) {
            $camposDuplicados[] = 'telefono';
        }

        return [
            'es_duplicado' => true,
            'campos_duplicados' => $camposDuplicados,
            'mensaje' => 'Persona duplicada encontrada en el sistema',
            'persona_existente' => $personaDuplicada
        ];
    }

    private function construirRespuestaImportacion(
        array $competidoresCreados,
        array $competidoresDuplicados,
        array $competidoresConError,
        ArchivoCsv $archivoCsv,
        string $gestion
    ): array {
        usort($competidoresCreados, function($a, $b) {
            return $a['indice'] <=> $b['indice'];
        });

        usort($competidoresDuplicados, function($a, $b) {
            return $a['indice'] <=> $b['indice'];
        });

        usort($competidoresConError, function($a, $b) {
            return $a['indice'] <=> $b['indice'];
        });

        $response = [
            'success' => true,
            'message' => 'Importación completada',
            'data' => [
                'total_importados' => count($competidoresCreados),
                'total_duplicados' => count($competidoresDuplicados),
                'total_errores' => count($competidoresConError),
                'archivo_csv_id' => $archivoCsv->id_archivo_csv,
                'archivo_csv_nombre' => $archivoCsv->nombre,
                'olimpiada_gestion' => $gestion,
                'competidores_creados' => array_column($competidoresCreados, 'persona')
            ]
        ];

        if (count($competidoresDuplicados) > 0) {
            $response['duplicados'] = $competidoresDuplicados;
        }

        if (count($competidoresConError) > 0) {
            $response['errores'] = $competidoresConError;
        }

        $mensajeResumen = "El archivo '{$archivoCsv->nombre}' ha sido importado para la gestión $gestion. " . 
            count($competidoresCreados) . " competidores han sido registrados";

        if (count($competidoresDuplicados) > 0) {
            $mensajeResumen .= ", " . count($competidoresDuplicados) . " han sido omitidos por duplicidad";
        }

        if (count($competidoresConError) > 0) {
            $mensajeResumen .= ", " . count($competidoresConError) . " contienen errores y no fueron importados";
        }

        $response['message'] = $mensajeResumen;

        return $response;
    }

}