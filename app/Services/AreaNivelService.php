<?php

namespace App\Services;

use App\Model\AreaNivel;
use App\Model\Olimpiada;
use App\Model\AreaOlimpiada;
use App\Model\Area;
use App\Model\Nivel;
use App\Model\GradoEscolaridad;
use App\Model\NivelGrado; // Nuevo modelo a usar
use App\Repositories\AreaNivelRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class AreaNivelService
{
    protected $areaNivelRepository;

    public function __construct(AreaNivelRepository $areaNivelRepository)
    {
        $this->areaNivelRepository = $areaNivelRepository;
    }

    /**
     * Obtiene y/o crea la olimpiada de la gestión actual.
     */
    private function obtenerOlimpiadaActual(): Olimpiada
    {
        $gestionActual = date('Y');
        $nombreOlimpiada = "Olimpiada Científica Estudiantil $gestionActual";

        return Olimpiada::firstOrCreate(
            ['gestion_olimp' => "$gestionActual"], // Columna corregida
            ['nombre_olimp' => $nombreOlimpiada] // Columna corregida
        );
    }

    public function getAreaNivelList(): Collection
    {
        return $this->areaNivelRepository->getAllAreasNiveles();
    }

    public function getAreaNivelByArea(int $id_area): Collection
    {
        $olimpiadaActual = $this->obtenerOlimpiadaActual();
        return $this->areaNivelRepository->getByArea($id_area, $olimpiadaActual->id_olimpiada);
    }

    public function getAreaNivelById(int $id): ?array
    {
        $areaNivel = $this->areaNivelRepository->getById($id);

        if (!$areaNivel) {
            return null;
        }

        // Mapeo de salida
        return [
            'id_area_nivel' => $areaNivel->id_area_nivel,
            'id_area' => $areaNivel->areaOlimpiada->id_area,
            'nombre_area' => $areaNivel->areaOlimpiada->area->nombre_area,
            'id_nivel' => $areaNivel->id_nivel,
            'nombre_nivel' => $areaNivel->nivel->nombre_nivel,
            'gestion' => $areaNivel->areaOlimpiada->olimpiada->gestion_olimp,
            'activo' => (bool)$areaNivel->es_activo_area_nivel, // Columna corregida
            'message' => 'Relación área-nivel encontrada'
        ];
    }

    public function getAreaNivelByAreaAll(int $id_area): Collection
    {
        $olimpiadaActual = $this->obtenerOlimpiadaActual();
        return $this->areaNivelRepository->getByAreaAll($id_area, $olimpiadaActual->id_olimpiada);
    }

    public function getAreaNivelesAsignadosAll(): array
    {
        $olimpiadaActual = $this->obtenerOlimpiadaActual();
        // El repositorio ya retorna las columnas con alias (nombre, gestion)
        $areas = $this->areaNivelRepository->getAreaNivelAsignadosAll($olimpiadaActual->id_olimpiada);

        $resultado = $areas->map(function($area) {

            // Acceso a relaciones anidadas, asumiendo que el Repositorio cargó AreaOlimpiadas.AreaNiveles.Nivel
            $nivelesArray = $area->areaOlimpiadas->flatMap(fn($ao) => $ao->areaNiveles)
                ->filter(fn($an) => $an->es_activo_area_nivel) // Columna corregida
                ->map(fn($areaNivel) => [
                    'id_nivel' => $areaNivel->nivel->id_nivel,
                    'nombre' => $areaNivel->nivel->nombre_nivel, // Columna corregida
                    'asignado_activo' => $areaNivel->es_activo_area_nivel // Columna corregida
                ])->values();

            return [
                'id_area' => $area->id_area,
                'nombre' => $area->nombre_area, // Columna corregida
                'niveles' => $nivelesArray
            ];
        });

        return [
            'areas' => $resultado->values(),
            'olimpiada_actual' => $olimpiadaActual->gestion_olimp, // Columna corregida
            'message' => 'Se muestran las áreas que tienen al menos una relación activa'
        ];
    }

    /**
     * Crea múltiples relaciones AreaNivel + NivelGrado.
     */
    public function createMultipleAreaNivel(array $data): array
    {
        Log::info('[SERVICE] INICIANDO createMultipleAreaNivel:', ['input_count' => count($data)]);

        if (!is_array($data) || empty($data)) {
            Log::warning('[SERVICE] Datos inválidos o vacíos recibidos');
            throw new Exception('Los datos no son un array válido o están vacíos.');
        }

        return DB::transaction(function () use ($data) {
            $olimpiadaActual = $this->obtenerOlimpiadaActual();
            $insertedAreaNiveles = [];
            $errors = [];

            foreach ($data as $index => $relacion) {
                try {
                    $area = Area::find($relacion['id_area']);
                    $nivel = Nivel::find($relacion['id_nivel']);
                    $gradoEscolaridad = GradoEscolaridad::find($relacion['id_grado_escolaridad']);

                    if (!$area || !$nivel || !$gradoEscolaridad) {
                        throw new Exception("Error en IDs (Área: {$relacion['id_area']}, Nivel: {$relacion['id_nivel']}, Grado: {$relacion['id_grado_escolaridad']}).");
                    }

                    // 1. Encontrar o crear AreaOlimpiada
                    $areaOlimpiada = AreaOlimpiada::firstOrCreate([
                        'id_area' => $relacion['id_area'],
                        'id_olimpiada' => $olimpiadaActual->id_olimpiada,
                    ]);

                    // 2. Encontrar o crear AreaNivel
                    $areaNivel = AreaNivel::firstOrCreate(
                        [
                            'id_area_olimpiada' => $areaOlimpiada->id_area_olimpiada,
                            'id_nivel' => $relacion['id_nivel'],
                        ],
                        [
                            'es_activo_area_nivel' => $relacion['activo'] ?? true // Columna corregida
                        ]
                    );

                    // 3. Crear relación NivelGrado (El puente con el grado)
                    NivelGrado::firstOrCreate([
                        'id_area_nivel' => $areaNivel->id_area_nivel, // Columna corregida
                        'id_grado_escolaridad' => $relacion['id_grado_escolaridad'],
                    ]);

                    $insertedAreaNiveles[] = $areaNivel;

                } catch (Exception $e) {
                    $errorMsg = "Relación {$index}: Error al crear/asociar - " . $e->getMessage();
                    $errors[] = $errorMsg;
                    Log::error("[SERVICE] {$errorMsg}");
                }
            }

            // Mapeo de resultados
            $message = count($insertedAreaNiveles) > 0
                ? "Se crearon/actualizaron correctamente " . count($insertedAreaNiveles) . " relaciones."
                : "Ninguna relación pudo ser procesada.";

            if (count($errors) > 0) {
                 $message .= " Se encontraron algunos errores en " . count($errors) . " relaciones.";
            }

            return [
                'area_niveles' => $insertedAreaNiveles,
                'olimpiada' => $olimpiadaActual->gestion_olimp, // Columna corregida
                'message' => $message,
                'errors' => $errors,
                'success_count' => count($insertedAreaNiveles),
                'error_count' => count($errors),
                'distribucion' => []
            ];
        });
    }

    /**
     * Actualiza el estado 'activo' de AreaNivel y crea la relación si no existe.
     */
    public function updateAreaNivelByArea(int $id_area, array $niveles): array
    {
        return DB::transaction(function () use ($id_area, $niveles) {
            $olimpiadaActual = $this->obtenerOlimpiadaActual();
            $updatedNiveles = [];

            // 1. Obtener/Crear AreaOlimpiada
            $areaOlimpiada = AreaOlimpiada::firstOrCreate([
                'id_area' => $id_area,
                'id_olimpiada' => $olimpiadaActual->id_olimpiada,
            ]);

            foreach ($niveles as $nivelData) {
                // NOTA: La estructura de la tabla ya no soporta id_grado_escolaridad en AreaNivel,
                // solo en NivelGrado. Este método solo actualiza el estado de la asociación AreaNivel.

                // 2. Buscar AreaNivel por id_area_olimpiada y id_nivel
                $areaNivel = AreaNivel::where('id_area_olimpiada', $areaOlimpiada->id_area_olimpiada)
                    ->where('id_nivel', $nivelData['id_nivel'])
                    ->first();

                $dataToUpdate = ['es_activo_area_nivel' => $nivelData['activo']]; // Columna corregida

                if ($areaNivel) {
                    $areaNivel->update($dataToUpdate);
                    $updatedNiveles[] = $areaNivel;
                } else {
                    // Crea si no existe. Esto solo debe ocurrir si existe un Grado asociado
                    // (lo cual se debe gestionar en la creación inicial, no en la activación/desactivación).
                    // Asumo que si se pasa id_grado_escolaridad, se desea crear la asociación completa.

                    $newAreaNivel = AreaNivel::create([
                        'id_area_olimpiada' => $areaOlimpiada->id_area_olimpiada,
                        'id_nivel' => $nivelData['id_nivel'],
                        'es_activo_area_nivel' => $nivelData['activo'] // Columna corregida
                    ]);

                    // Si se proporcionó id_grado_escolaridad, se crea también NivelGrado
                    if (isset($nivelData['id_grado_escolaridad'])) {
                         NivelGrado::firstOrCreate([
                            'id_area_nivel' => $newAreaNivel->id_area_nivel,
                            'id_grado_escolaridad' => $nivelData['id_grado_escolaridad'],
                        ]);
                    }

                    $updatedNiveles[] = $newAreaNivel;
                }
            }

            return [
                'area_niveles' => $updatedNiveles,
                'olimpiada' => $olimpiadaActual->gestion_olimp, // Columna corregida
                'message' => 'Relaciones área-nivel actualizadas exitosamente para la gestión actual'
            ];
        });
    }

    public function updateAreaNivel(int $id, array $data): array
    {
        $areaNivel = AreaNivel::find($id);

        if (!$areaNivel) {
            throw new Exception('Relación área-nivel no encontrada');
        }

        // Mapeo de columna 'activo' a 'es_activo_area_nivel'
        if (isset($data['activo'])) {
            $data['es_activo_area_nivel'] = $data['activo'];
            unset($data['activo']);
        }

        $areaNivel->update($data);

        return [
            'area_nivel' => $areaNivel,
            'message' => 'Relación área-nivel actualizada exitosamente'
        ];
    }

    public function deleteAreaNivel(int $id): array
    {
        $areaNivel = AreaNivel::find($id);

        if (!$areaNivel) {
            throw new Exception('Relación área-nivel no encontrada');
        }

        // Adicionalmente, eliminar entradas en NivelGrado (si existen)
        NivelGrado::where('id_area_nivel', $id)->delete();

        $areaNivel->delete();

        return [
            'message' => 'Relación área-nivel y sus grados asociados eliminados exitosamente'
        ];
    }

    public function getAreasConNivelesSimplificado(): array
    {
        $olimpiadaActual = $this->obtenerOlimpiadaActual();
        // Repositorio ya retorna la data pre-formateada con alias
        $areas = $this->areaNivelRepository->getAreasConNivelesSimplificado($olimpiadaActual->id_olimpiada);

        // Cargar detalles de Grado Escolaridad a través de NivelGrado (necesario para el mapeo final)
        $areas->each(function($area) {
            $area->load(['areaOlimpiadas.areaNiveles.nivelGrados.gradoEscolaridad']);
        });

        $resultado = $areas->map(function($area) {

            // Usamos AreaOlimpiadas para acceder a AreaNiveles
            $areaNiveles = $area->areaOlimpiadas->flatMap(fn($ao) => $ao->areaNiveles)
                ->filter(fn($an) => $an->es_activo_area_nivel);

            $nivelesAgrupados = $areaNiveles->groupBy('id_nivel')->map(function($areaNivelesPorNivel) {
                $primerNivel = $areaNivelesPorNivel->first();
                $grados = $areaNivelesPorNivel->flatMap(fn($an) => $an->nivelGrados)
                    ->map(fn($ng) => [
                        'id_grado_escolaridad' => $ng->gradoEscolaridad->id_grado_escolaridad,
                        'nombre_grado' => $ng->gradoEscolaridad->nombre_grado // Columna corregida
                    ])->unique('id_grado_escolaridad')->values();

                return [
                    'id_nivel' => $primerNivel->id_nivel,
                    'nombre_nivel' => $primerNivel->nivel->nombre_nivel, // Columna corregida
                    'grados' => $grados
                ];
            });

            return [
                'id_area' => $area->id_area,
                'nombre' => $area->nombre_area, // Columna corregida
                'niveles' => $nivelesAgrupados->values()
            ];
        });

        return [
            'areas' => $resultado->values(),
            'olimpiada_actual' => $olimpiadaActual->gestion_olimp, // Columna corregida
            'message' => 'Áreas con niveles y grados activos obtenidas exitosamente'
        ];
    }

    public function getAreasConNivelesPorOlimpiada(int $idOlimpiada): array
    {
        $olimpiada = Olimpiada::findOrFail($idOlimpiada);
        // Repositorio ya retorna la data pre-formateada con alias
        $areas = $this->areaNivelRepository->getAreasConNivelesSimplificado($idOlimpiada);

        // Cargar detalles de Grado Escolaridad a través de NivelGrado (necesario para el mapeo final)
        $areas->each(function($area) {
            $area->load(['areaOlimpiadas.areaNiveles.nivelGrados.gradoEscolaridad']);
        });

        $resultado = $areas->map(function($area) {

            // Usamos AreaOlimpiadas para acceder a AreaNiveles
            $areaNiveles = $area->areaOlimpiadas->flatMap(fn($ao) => $ao->areaNiveles)
                ->filter(fn($an) => $an->es_activo_area_nivel); // Columna corregida

            $nivelesAgrupados = $areaNiveles->groupBy('id_nivel')->map(function($areaNivelesPorNivel) {
                $primerNivel = $areaNivelesPorNivel->first();
                $grados = $areaNivelesPorNivel->flatMap(fn($an) => $an->nivelGrados)
                    ->map(fn($ng) => [
                        'id_grado_escolaridad' => $ng->gradoEscolaridad->id_grado_escolaridad,
                        'nombre_grado' => $ng->gradoEscolaridad->nombre_grado // Columna corregida
                    ])->unique('id_grado_escolaridad')->values();

                return [
                    'id_nivel' => $primerNivel->id_nivel,
                    'nombre_nivel' => $primerNivel->nivel->nombre_nivel, // Columna corregida
                    'grados' => $grados
                ];
            });

            return [
                'id_area' => $area->id_area,
                'nombre' => $area->nombre_area, // Columna corregida
                'niveles' => $nivelesAgrupados->values()
            ];
        });

        return [
            'areas' => $resultado->values(),
            'olimpiada' => $olimpiada->gestion_olimp, // Columna corregida
            'message' => "Áreas con niveles y grados activos obtenidas para la gestión {$olimpiada->gestion_olimp}"
        ];
    }

    public function getAreasConNivelesPorGestion(string $gestion): array
    {
        $olimpiada = Olimpiada::where('gestion_olimp', $gestion)->firstOrFail(); // Columna corregida
        return $this->getAreasConNivelesPorOlimpiada($olimpiada->id_olimpiada);
    }

    public function getAllAreaNivelWithDetails(): array
    {
        $areaNiveles = AreaNivel::with([
            // Rutas corregidas
            'areaOlimpiada.area:id_area,nombre_area as nombre',
            'nivel:id_nivel,nombre_nivel as nombre',
            'nivelGrados.gradoEscolaridad:id_grado_escolaridad,nombre_grado as nombre', // Acceso a Grado a través de NivelGrado
            'areaOlimpiada.olimpiada:id_olimpiada,gestion_olimp as gestion'
        ])->get();

        return [
            'area_niveles' => $areaNiveles,
            'message' => 'Todas las relaciones área-nivel obtenidas con detalles'
        ];
    }

    public function getNivelesGradosByAreaAndGestion(int $id_area, string $gestion): array
    {
        try {
            $olimpiada = Olimpiada::where('gestion_olimp', $gestion)->first(); // Columna corregida

            if (!$olimpiada) {
                return [
                    'success' => false,
                    'data' => [],
                    'message' => "No se encontró la olimpiada con gestión: {$gestion}"
                ];
            }

            $area = Area::find($id_area);
            if (!$area) {
                return [
                    'success' => false,
                    'data' => [],
                    'message' => "No se encontró el área con ID: {$id_area}"
                ];
            }

            // Cargar relaciones filtradas y anidadas
            $areaNiveles = AreaNivel::whereHas('areaOlimpiada', fn($q) => $q->where('id_area', $id_area)->where('id_olimpiada', $olimpiada->id_olimpiada))
                ->with([
                    'nivel:id_nivel,nombre_nivel as nombre',
                    'nivelGrados.gradoEscolaridad:id_grado_escolaridad,nombre_grado as nombre' // Carga corregida
                ])
                ->where('es_activo_area_nivel', true) // Columna corregida
                ->get();

            // Lógica de mapeo y agrupación (se mantiene la estructura compleja solicitada)
            $nivelesGrados = $areaNiveles->map(function($areaNivel) {
                $grados = $areaNivel->nivelGrados->map(fn($ng) => [
                    'id_grado_escolaridad' => $ng->gradoEscolaridad->id_grado_escolaridad,
                    'nombre' => $ng->gradoEscolaridad->nombre_grado // Columna corregida
                ])->toArray();

                return [
                    'id_area_nivel' => $areaNivel->id_area_nivel,
                    'nivel' => [
                        'id_nivel' => $areaNivel->nivel->id_nivel,
                        'nombre' => $areaNivel->nivel->nombre // Ya tiene alias
                    ],
                    'grados_escolaridad' => $grados,
                    'activo' => (bool)$areaNivel->es_activo_area_nivel
                ];
            });


            $nivelesAgrupados = $areaNiveles->groupBy('id_nivel')->map(function($areaNivelesPorNivel) {
                $primerNivel = $areaNivelesPorNivel->first();
                $grados = $areaNivelesPorNivel->flatMap(fn($an) => $an->nivelGrados)
                    ->map(fn($ng) => [
                        'id_grado_escolaridad' => $ng->gradoEscolaridad->id_grado_escolaridad,
                        'nombre' => $ng->gradoEscolaridad->nombre_grado // Columna corregida
                    ])->unique('id_grado_escolaridad')->values();

                return [
                    'id_nivel' => $primerNivel->nivel->id_nivel,
                    'nombre_nivel' => $primerNivel->nivel->nombre,
                    'grados' => $grados
                ];
            });

            return [
                'success' => true,
                'data' => [
                    'area' => [
                        'id_area' => $area->id_area,
                        'nombre' => $area->nombre_area // Columna corregida
                    ],
                    'olimpiada' => [
                        'id_olimpiada' => $olimpiada->id_olimpiada,
                        'gestion' => $olimpiada->gestion_olimp, // Columna corregida
                        'nombre' => $olimpiada->nombre_olimp // Columna corregida
                    ],
                    'niveles_individuales' => $nivelesGrados->values(),
                    'niveles_con_grados_agrupados' => $nivelesAgrupados->values(),
                    'total_relaciones' => $areaNiveles->count(),
                    'total_niveles' => $nivelesAgrupados->count()
                ],
                'message' => "Niveles y grados obtenidos exitosamente para el área {$area->nombre_area} en la gestión {$gestion}"
            ];

        } catch (Exception $e) {
            Log::error('[SERVICE] Error al obtener niveles y grados por área y gestión:', [
                'id_area' => $id_area,
                'gestion' => $gestion,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'data' => [],
                'message' => 'Error al obtener los niveles y grados: ' . $e->getMessage()
            ];
        }
    }

    public function getNivelesGradosByAreasAndGestion(array $id_areas, string $gestion): array
    {
        try {
            $olimpiada = Olimpiada::where('gestion_olimp', $gestion)->first(); // Columna corregida

            if (!$olimpiada) {
                return [
                    'success' => false,
                    'data' => [],
                    'message' => "No se encontró la olimpiada con gestión: {$gestion}"
                ];
            }

            $areas = Area::whereIn('id_area', $id_areas)->get();
            if ($areas->isEmpty()) {
                return [
                    'success' => false,
                    'data' => [],
                    'message' => "No se encontraron las áreas con los IDs proporcionados"
                ];
            }

            // Cargar relaciones filtradas
            $areaNiveles = AreaNivel::whereHas('areaOlimpiada', fn($q) => $q->whereIn('id_area', $id_areas)->where('id_olimpiada', $olimpiada->id_olimpiada))
                ->with([
                    'areaOlimpiada.area:id_area,nombre_area as nombre', // Carga anidada
                    'nivel:id_nivel,nombre_nivel as nombre',
                    'nivelGrados.gradoEscolaridad:id_grado_escolaridad,nombre_grado as nombre'
                ])
                ->where('es_activo_area_nivel', true) // Columna corregida
                ->get();

            // Agrupar los AreaNiveles por Area (porque se pasó un array de áreas)
            $areaNivelesGroupedByArea = $areaNiveles->groupBy(fn($an) => $an->areaOlimpiada->id_area);


            $resultadoPorArea = $areas->map(function($area) use ($areaNivelesGroupedByArea, $olimpiada) {
                $relacionesArea = $areaNivelesGroupedByArea->get($area->id_area, collect());

                $nivelesAgrupados = $relacionesArea->groupBy('id_nivel')->map(function($areaNivelesPorNivel) {
                    $primerNivel = $areaNivelesPorNivel->first();

                    $grados = $areaNivelesPorNivel->flatMap(fn($an) => $an->nivelGrados)
                        ->map(fn($ng) => [
                            'id_grado_escolaridad' => $ng->gradoEscolaridad->id_grado_escolaridad,
                            'nombre' => $ng->gradoEscolaridad->nombre_grado // Columna corregida
                        ])->unique('id_grado_escolaridad')->values();

                    return [
                        'id_nivel' => $primerNivel->nivel->id_nivel,
                        'nombre_nivel' => $primerNivel->nivel->nombre,
                        'grados' => $grados
                    ];
                });

                return [
                    'area' => [
                        'id_area' => $area->id_area,
                        'nombre' => $area->nombre_area // Columna corregida
                    ],
                    'niveles_agrupados' => $nivelesAgrupados->values(),
                    'total_relaciones' => $relacionesArea->count(),
                    'total_niveles' => $nivelesAgrupados->count()
                ];
            });

            return [
                'success' => true,
                'data' => [
                    'olimpiada' => [
                        'id_olimpiada' => $olimpiada->id_olimpiada,
                        'gestion' => $olimpiada->gestion_olimp, // Columna corregida
                        'nombre' => $olimpiada->nombre_olimp // Columna corregida
                    ],
                    'areas' => $resultadoPorArea->values(),
                    'total_areas' => $areas->count(),
                    'total_relaciones' => $areaNiveles->count()
                ],
                'message' => "Niveles y grados obtenidos exitosamente para {$areas->count()} áreas en la gestión {$gestion}"
            ];

        } catch (Exception $e) {
            Log::error('[SERVICE] Error al obtener niveles y grados por áreas y gestión:', [
                'id_areas' => $id_areas,
                'gestion' => $gestion,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'data' => [],
                'message' => 'Error al obtener los niveles y grados: ' . $e->getMessage()
            ];
        }
    }

    public function getAreaNivelActuales(): array
    {
        $olimpiadaActual = $this->obtenerOlimpiadaActual();
        // El repositorio ya retorna la data pre-formateada con alias
        $areas = $this->areaNivelRepository->getActualesByOlimpiada($olimpiadaActual->id_olimpiada);

        $resultado = $areas->map(function($area) {
            // Navegación corregida para obtener AreaNivel
            $niveles = $area->areaOlimpiadas->flatMap(fn($ao) => $ao->areaNiveles)
                ->filter(fn($an) => $an->es_activo_area_nivel) // Columna corregida
                ->map(fn($areaNivel) => [
                    'id_area_nivel' => $areaNivel->id_area_nivel,
                    'id_nivel' => $areaNivel->id_nivel,
                    'nombre' => $areaNivel->nivel->nombre_nivel // Columna corregida
                ])->values();

            return [
                'id_area' => $area->id_area,
                'area' => $area->nombre_area, // Columna corregida
                'niveles' => $niveles->values()
            ];
        });

        return $resultado->values()->all();
    }
}
