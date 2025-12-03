<?php

namespace App\Services;

use App\Model\AreaNivel;
use App\Model\Olimpiada;
use App\Model\AreaOlimpiada;
use App\Model\Area;
use App\Model\Nivel;
use App\Model\GradoEscolaridad;
use App\Repositories\AreaNivelGradoRepository;
use App\Repositories\AreaNivelRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AreaNivelGradoService
{
    protected $areaNivelGradoRepository;
    protected $areaNivelRepository;

    public function __construct(
        AreaNivelGradoRepository $areaNivelGradoRepository,
        AreaNivelRepository $areaNivelRepository
    ) {
        $this->areaNivelGradoRepository = $areaNivelGradoRepository;
        $this->areaNivelRepository = $areaNivelRepository;
    }

    private function obtenerOlimpiadaActual(): Olimpiada
    {
        $gestionActual = date('Y');
        $nombreOlimpiada = "Olimpiada Científica Estudiantil $gestionActual";
        
        return Olimpiada::firstOrCreate(
            ['gestion' => "$gestionActual"],
            ['nombre' => $nombreOlimpiada]
        );
    }

    // Método para la ruta GET /area-nivel (índice)
    public function index(): array
    {
        return $this->getAreasConNivelesSimplificado();
    }

    public function createMultipleAreaNivelWithGrades(array $data): array
    {
        Log::info('[SERVICE] INICIANDO createMultipleAreaNivelWithGrades:', [
            'input_data' => $data,
            'input_count' => count($data),
        ]);

        if (!is_array($data) || empty($data)) {
            Log::warning('[SERVICE] Datos inválidos o vacíos recibidos');
            return [
                'area_niveles' => [],
                'olimpiada' => 'N/A',
                'message' => 'Error: Los datos no son un array válido o están vacíos',
                'errors' => ['Formato de datos inválido'],
                'success_count' => 0,
                'error_count' => 1,
            ];
        }

        DB::beginTransaction();
        try {
            $olimpiadaActual = $this->obtenerOlimpiadaActual();
            $inserted = [];
            $errors = [];

            $grupos = [];
            foreach ($data as $index => $relacion) {
                $clave = $relacion['id_area'] . '_' . $relacion['id_nivel'];
                
                if (!isset($grupos[$clave])) {
                    $grupos[$clave] = [
                        'id_area' => $relacion['id_area'],
                        'id_nivel' => $relacion['id_nivel'],
                        'es_activo' => $relacion['activo'],
                        'grados' => []
                    ];
                }
                
                $grupos[$clave]['grados'][] = $relacion['id_grado_escolaridad'];
            }

            foreach ($grupos as $clave => $grupo) {
                try {
                    $areaOlimpiada = AreaOlimpiada::firstOrCreate(
                        [
                            'id_area' => $grupo['id_area'],
                            'id_olimpiada' => $olimpiadaActual->id_olimpiada
                        ]
                    );
                    
                    $areaNivel = AreaNivel::firstOrCreate(
                        [
                            'id_area_olimpiada' => $areaOlimpiada->id_area_olimpiada,
                            'id_nivel' => $grupo['id_nivel']
                        ],
                        [
                            'es_activo' => $grupo['es_activo']
                        ]
                    );

                    // Asignar grados (sync mantiene solo los grados proporcionados)
                    $areaNivel->gradosEscolaridad()->sync($grupo['grados']);

                    $inserted[] = $areaNivel->load(['areaOlimpiada.area', 'nivel', 'gradosEscolaridad']);

                } catch (\Exception $e) {
                    $errorMsg = "Error en grupo {$clave}: " . $e->getMessage();
                    $errors[] = $errorMsg;
                    Log::error("[SERVICE] {$errorMsg}");
                }
            }

            DB::commit();

            $message = '';
            if (count($inserted) > 0) {
                $message = "Se crearon/actualizaron " . count($inserted) . " relaciones área-nivel-grado correctamente";
            }
            
            if (count($errors) > 0) {
                $message .= ". Se encontraron " . count($errors) . " errores.";
            }

            return [
                'area_niveles' => $inserted,
                'olimpiada' => $olimpiadaActual->gestion,
                'message' => $message,
                'errors' => $errors,
                'success_count' => count($inserted),
                'error_count' => count($errors)
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[SERVICE] Error general en createMultipleAreaNivelWithGrades:', [
                'exception' => $e->getMessage()
            ]);
            throw new \Exception("Error al procesar relaciones: " . $e->getMessage());
        }
    }

    public function getNivelesGradosByAreaAndGestion(int $id_area, string $gestion): array
    {
        try {
            $olimpiada = Olimpiada::where('gestion', $gestion)->first();

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

            $areaOlimpiada = AreaOlimpiada::where('id_area', $id_area)
                ->where('id_olimpiada', $olimpiada->id_olimpiada)
                ->first();

            if (!$areaOlimpiada) {
                return [
                    'success' => false,
                    'data' => [],
                    'message' => "El área no está asignada a la olimpiada de gestión {$gestion}"
                ];
            }

            $areaNiveles = AreaNivel::with([
                'nivel:id_nivel,nombre',
                'gradosEscolaridad:id_grado_escolaridad,nombre'
            ])
            ->where('id_area_olimpiada', $areaOlimpiada->id_area_olimpiada)
            ->where('es_activo', true)
            ->get();

            $nivelesAgrupados = $areaNiveles->map(function($areaNivel) {
                return [
                    'id_area_nivel' => $areaNivel->id_area_nivel,
                    'nivel' => [
                        'id_nivel' => $areaNivel->nivel->id_nivel,
                        'nombre' => $areaNivel->nivel->nombre
                    ],
                    'grados' => $areaNivel->gradosEscolaridad->map(function($grado) {
                        return [
                            'id_grado_escolaridad' => $grado->id_grado_escolaridad,
                            'nombre' => $grado->nombre
                        ];
                    })
                ];
            });

            return [
                'success' => true,
                'data' => [
                    'area' => [
                        'id_area' => $area->id_area,
                        'nombre' => $area->nombre
                    ],
                    'olimpiada' => [
                        'id_olimpiada' => $olimpiada->id_olimpiada,
                        'gestion' => $olimpiada->gestion,
                        'nombre' => $olimpiada->nombre
                    ],
                    'niveles_con_grados' => $nivelesAgrupados,
                    'total_niveles' => $areaNiveles->count(),
                    'total_relaciones' => $areaNiveles->sum(function($areaNivel) {
                        return $areaNivel->gradosEscolaridad->count();
                    })
                ],
                'message' => "Niveles y grados obtenidos exitosamente para el área {$area->nombre} en la gestión {$gestion}"
            ];

        } catch (\Exception $e) {
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

    // Método para la ruta GET /area-nivel/simplificado
    public function getAreasConNivelesSimplificado(): array
    {
        $olimpiadaActual = $this->obtenerOlimpiadaActual();
        
        // Corregido: usar 'areaOlimpiada' (singular) en lugar de 'areaOlimpiadas'
        $areas = Area::with([
            'areaOlimpiada' => function($query) use ($olimpiadaActual) {
                $query->where('id_olimpiada', $olimpiadaActual->id_olimpiada);
            },
            'areaOlimpiada.areaNiveles' => function($query) {
                $query->where('es_activo', true);
            },
            'areaOlimpiada.areaNiveles.nivel:id_nivel,nombre',
            'areaOlimpiada.areaNiveles.gradosEscolaridad:id_grado_escolaridad,nombre'
        ])
        ->whereHas('areaOlimpiada.areaNiveles', function($query) {
            $query->where('es_activo', true);
        })
        ->get(['id_area', 'nombre']);

        $resultado = $areas->map(function($area) {
            $nivelesAgrupados = collect();
            
            foreach ($area->areaOlimpiada as $areaOlimpiada) {
                foreach ($areaOlimpiada->areaNiveles as $areaNivel) {
                    $nivelesAgrupados->push([
                        'id_nivel' => $areaNivel->nivel->id_nivel,
                        'nombre_nivel' => $areaNivel->nivel->nombre,
                        'grados' => $areaNivel->gradosEscolaridad->map(function($grado) {
                            return [
                                'id_grado_escolaridad' => $grado->id_grado_escolaridad,
                                'nombre_grado' => $grado->nombre
                            ];
                        })->values()
                    ]);
                }
            }

            return [
                'id_area' => $area->id_area,
                'nombre' => $area->nombre,
                'niveles' => $nivelesAgrupados->unique('id_nivel')->values()
            ];
        });

        return [
            'areas' => $resultado->values(),
            'olimpiada_actual' => $olimpiadaActual->gestion,
            'message' => 'Áreas con niveles y grados activos obtenidas exitosamente'
        ];
    }

    // Método para la ruta POST /area-nivel/gestion/{gestion}/areas
     public function getNivelesGradosByAreasAndGestion(int $id_area, string $gestion): array
    {
        try {
            Log::info('[SERVICE] Obteniendo niveles y grados por área y gestión', [
                'id_area' => $id_area,
                'gestion' => $gestion
            ]);

            $olimpiada = Olimpiada::where('gestion', $gestion)->first();

            if (!$olimpiada) {
                Log::warning('[SERVICE] Olimpiada no encontrada', ['gestion' => $gestion]);
                return [
                    'success' => false,
                    'data' => [],
                    'message' => "No se encontró la olimpiada con gestión: {$gestion}"
                ];
            }

            $area = Area::find($id_area);
            if (!$area) {
                Log::warning('[SERVICE] Área no encontrada', ['id_area' => $id_area]);
                return [
                    'success' => false,
                    'data' => [],
                    'message' => "No se encontró el área con ID: {$id_area}"
                ];
            }

            $areaOlimpiada = AreaOlimpiada::where('id_area', $id_area)
                ->where('id_olimpiada', $olimpiada->id_olimpiada)
                ->first();

            if (!$areaOlimpiada) {
                Log::warning('[SERVICE] Relación área-olimpiada no encontrada', [
                    'id_area' => $id_area,
                    'id_olimpiada' => $olimpiada->id_olimpiada
                ]);
                return [
                    'success' => false,
                    'data' => [],
                    'message' => "El área no está asignada a la olimpiada de gestión {$gestion}"
                ];
            }

            $areaNiveles = AreaNivel::with([
                'nivel:id_nivel,nombre',
                'gradosEscolaridad:id_grado_escolaridad,nombre'
            ])
            ->where('id_area_olimpiada', $areaOlimpiada->id_area_olimpiada)
            ->where('es_activo', true)
            ->get();

            Log::info('[SERVICE] AreaNiveles encontrados', [
                'count' => $areaNiveles->count(),
                'ids' => $areaNiveles->pluck('id_area_nivel')->toArray()
            ]);

            $nivelesMap = [];
            
            foreach ($areaNiveles as $areaNivel) {
                $idNivel = $areaNivel->nivel->id_nivel;
                
                if (!isset($nivelesMap[$idNivel])) {
                    $nivelesMap[$idNivel] = [
                        'id_nivel' => $idNivel,
                        'nombre_nivel' => $areaNivel->nivel->nombre,
                        'grados' => []
                    ];
                }
                
                foreach ($areaNivel->gradosEscolaridad as $grado) {
                    $nivelesMap[$idNivel]['grados'][] = [
                        'id_grado_escolaridad' => $grado->id_grado_escolaridad,
                        'nombre' => $grado->nombre
                    ];
                }
            }

            foreach ($nivelesMap as &$nivelData) {
                $nivelData['grados'] = collect($nivelData['grados'])
                    ->unique('id_grado_escolaridad')
                    ->values()
                    ->toArray();
            }

            $nivelesIndividuales = $areaNiveles->map(function($areaNivel) {
                return [
                    'id_area_nivel' => $areaNivel->id_area_nivel,
                    'nivel' => [
                        'id_nivel' => $areaNivel->nivel->id_nivel,
                        'nombre' => $areaNivel->nivel->nombre
                    ]
                ];
            })->values();

            $response = [
                'success' => true,
                'data' => [
                    'niveles_con_grados_agrupados' => array_values($nivelesMap),
                    'niveles_individuales' => $nivelesIndividuales->toArray()
                ]
            ];

            Log::info('[SERVICE] Respuesta formateada', [
                'niveles_agrupados_count' => count($nivelesMap),
                'niveles_individuales_count' => $nivelesIndividuales->count()
            ]);

            return $response;

        } catch (\Exception $e) {
            Log::error('[SERVICE] Error al obtener niveles y grados por área y gestión:', [
                'id_area' => $id_area,
                'gestion' => $gestion,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'data' => [],
                'message' => 'Error al obtener los niveles y grados: ' . $e->getMessage()
            ];
        }
    }

    // Método para la ruta POST /area-nivel/por-gestion
    public function getByGestionAndAreas(string $gestion, array $idAreas): array
    {
        $olimpiada = Olimpiada::where('gestion', $gestion)->firstOrFail();
        
        $areaNiveles = AreaNivel::whereHas('areaOlimpiada', function($query) use ($idAreas, $olimpiada) {
                $query->whereIn('id_area', $idAreas)
                      ->where('id_olimpiada', $olimpiada->id_olimpiada);
            })
            ->with(['areaOlimpiada.area', 'nivel', 'gradosEscolaridad'])
            ->get();

        return [
            'area_niveles' => $areaNiveles,
            'olimpiada' => $olimpiada->gestion,
            'message' => "Relaciones área-nivel obtenidas para la gestión {$gestion}"
        ];
    }

    // GET /area-niveles/{id_area}
    public function getAreaNivelByAreaAll(int $id_area): array
    {
        $olimpiadaActual = $this->obtenerOlimpiadaActual();
        
        $areaNiveles = AreaNivel::whereHas('areaOlimpiada', function($query) use ($id_area, $olimpiadaActual) {
                $query->where('id_area', $id_area)
                      ->where('id_olimpiada', $olimpiadaActual->id_olimpiada);
            })
            ->with(['nivel', 'gradosEscolaridad', 'areaOlimpiada.area'])
            ->get();

        return [
            'success' => true,
            'data' => $areaNiveles,
            'message' => 'Relaciones área-nivel obtenidas para el área especificada'
        ];
    }

    // GET /areas-con-niveles
    public function getAreasConNiveles(): array
    {
        $olimpiadaActual = $this->obtenerOlimpiadaActual();
        
        $areas = Area::with([
            'areaOlimpiada' => function($query) use ($olimpiadaActual) {
                $query->where('id_olimpiada', $olimpiadaActual->id_olimpiada);
            },
            'areaOlimpiada.areaNiveles' => function($query) {
                $query->where('es_activo', true);
            },
            'areaOlimpiada.areaNiveles.nivel:id_nivel,nombre'
        ])
        ->get(['id_area', 'nombre']);

        $resultado = $areas->filter(function($area) {
            if ($area->areaOlimpiada->isEmpty()) {
                return true;
            }
            
            $tieneActivos = false;
            foreach ($area->areaOlimpiada as $areaOlimpiada) {
                if ($areaOlimpiada->areaNiveles->isNotEmpty()) {
                    $tieneActivos = true;
                    break;
                }
            }
            
            return $tieneActivos;
        })->map(function($area) {
            $nivelesArray = collect();
            foreach ($area->areaOlimpiada as $areaOlimpiada) {
                foreach ($areaOlimpiada->areaNiveles as $areaNivel) {
                    $nivelesArray->push([
                        'id_nivel' => $areaNivel->nivel->id_nivel,
                        'nombre' => $areaNivel->nivel->nombre,
                        'asignado_activo' => $areaNivel->es_activo
                    ]);
                }
            }

            if ($nivelesArray->isEmpty()) {
                return [
                    'id_area' => $area->id_area,
                    'nombre' => $area->nombre,
                    'niveles' => []
                ];
            }

            return [
                'id_area' => $area->id_area,
                'nombre' => $area->nombre,
                'niveles' => $nivelesArray->unique('id_nivel')->values()
            ];
        });

        return [
            'areas' => $resultado->values(),
            'olimpiada_actual' => $olimpiadaActual->gestion,
            'message' => 'Se muestran las áreas que tienen al menos una relación activa o no tienen relaciones'
        ];
    }

    // GET /area-nivel/gestion/{gestion}
    public function getAreasConNivelesPorGestion(string $gestion): array
    {
        $olimpiada = Olimpiada::where('gestion', $gestion)->firstOrFail();
        
        $areas = Area::with([
            'areaOlimpiada' => function($query) use ($olimpiada) {
                $query->where('id_olimpiada', $olimpiada->id_olimpiada);
            },
            'areaOlimpiada.areaNiveles' => function($query) {
                $query->where('es_activo', true);
            },
            'areaOlimpiada.areaNiveles.nivel:id_nivel,nombre',
            'areaOlimpiada.areaNiveles.gradosEscolaridad:id_grado_escolaridad,nombre'
        ])
        ->whereHas('areaOlimpiada.areaNiveles', function($query) {
            $query->where('es_activo', true);
        })
        ->get(['id_area', 'nombre']);

        $resultado = $areas->map(function($area) {
            $nivelesAgrupados = collect();
            foreach ($area->areaOlimpiada as $areaOlimpiada) {
                foreach ($areaOlimpiada->areaNiveles as $areaNivel) {
                    $nivelesAgrupados->push([
                        'id_nivel' => $areaNivel->nivel->id_nivel,
                        'nombre_nivel' => $areaNivel->nivel->nombre,
                        'grados' => $areaNivel->gradosEscolaridad->map(function($grado) {
                            return [
                                'id_grado_escolaridad' => $grado->id_grado_escolaridad,
                                'nombre_grado' => $grado->nombre
                            ];
                        })->values()
                    ]);
                }
            }

            return [
                'id_area' => $area->id_area,
                'nombre' => $area->nombre,
                'niveles' => $nivelesAgrupados->unique('id_nivel')->values()
            ];
        });

        return [
            'areas' => $resultado->values(),
            'olimpiada' => $olimpiada->gestion,
            'message' => "Áreas con niveles y grados activos obtenidas para la gestión {$gestion}"
        ];
    }

    // GET /area-nivel/olimpiada-con-grados/{id_olimpiada}
    public function getAreasConNivelesPorOlimpiada(int $idOlimpiada): array
    {
        $olimpiada = Olimpiada::findOrFail($idOlimpiada);
        return $this->getAreasConNivelesPorGestion($olimpiada->gestion);
    }
}