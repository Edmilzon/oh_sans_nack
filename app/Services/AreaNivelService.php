<?php

namespace App\Services;

use App\Model\AreaNivel;
use App\Model\Olimpiada;
use App\Model\AreaOlimpiada;
use App\Model\Area;
use App\Model\Nivel;
use App\Model\GradoEscolaridad;
use App\Repositories\AreaNivelRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class AreaNivelService
{
    protected $areaNivelRepository;

    public function __construct(AreaNivelRepository $areaNivelRepository)
    {
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

        return [
            'area_nivel' => $areaNivel,
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
        $areas = $this->areaNivelRepository->getAreaNivelAsignadosAll($olimpiadaActual->id_olimpiada);
    
        $resultado = $areas->filter(function($area) {
            if ($area->areaNiveles->isEmpty()) {
                return true;
            }
            
            return $area->areaNiveles->contains('activo', true);
        })->map(function($area) {
            
            if ($area->areaNiveles->isEmpty()) {
                return [
                    'id_area' => $area->id_area,
                    'nombre' => $area->nombre,
                    'niveles' => []
                ];
            }
            
            $nivelesArray = $area->areaNiveles->filter(function($areaNivel) {
                return $areaNivel->activo === true;
            })->map(function($areaNivel) {
                return [
                    'id_nivel' => $areaNivel->nivel->id_nivel,
                    'nombre' => $areaNivel->nivel->nombre,
                    'asignado_activo' => $areaNivel->activo
                ];
            });
            
            return [
                'id_area' => $area->id_area,
                'nombre' => $area->nombre,
                'niveles' => $nivelesArray->values()
            ];
        });
    
        return [
            'areas' => $resultado->values(),
            'olimpiada_actual' => $olimpiadaActual->gestion,
            'message' => 'Se muestran las áreas que tienen al menos una relación activa o no tienen relaciones'
        ];
    }

    public function createMultipleAreaNivel(array $data): array
    {
        Log::info('[SERVICE] INICIANDO createMultipleAreaNivel:', [
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

        try {
            $olimpiadaActual = $this->obtenerOlimpiadaActual();
            Log::info('[SERVICE] Olimpiada actual obtenida:', [
                'id_olimpiada' => $olimpiadaActual->id_olimpiada,
                'gestion' => $olimpiadaActual->gestion
            ]);
            
            $inserted = [];
            $errors = [];
            $totalRelations = count($data);
            
            Log::info("[SERVICE] Procesando {$totalRelations} relaciones");

            foreach ($data as $index => $relacion) {
                Log::info("[SERVICE] Procesando relación {$index}:", $relacion);
                
                try {
                    $area = Area::find($relacion['id_area']);
                    if (!$area) {
                        $errorMsg = "Relación {$index}: El área {$relacion['id_area']} no existe";
                        $errors[] = $errorMsg;
                        Log::warning("[SERVICE] {$errorMsg}");
                        continue;
                    }

                    $nivel = Nivel::find($relacion['id_nivel']);
                    if (!$nivel) {
                        $errorMsg = "Relación {$index}: El nivel {$relacion['id_nivel']} no existe";
                        $errors[] = $errorMsg;
                        Log::warning("[SERVICE] {$errorMsg}");
                        continue;
                    }

                    $gradoEscolaridad = GradoEscolaridad::find($relacion['id_grado_escolaridad']);
                    if (!$gradoEscolaridad) {
                        $errorMsg = "Relación {$index}: El grado de escolaridad {$relacion['id_grado_escolaridad']} no existe";
                        $errors[] = $errorMsg;
                        Log::warning("[SERVICE] {$errorMsg}");
                        continue;
                    }

                    $areaOlimpiada = AreaOlimpiada::where('id_area', $relacion['id_area'])
                        ->where('id_olimpiada', $olimpiadaActual->id_olimpiada)
                        ->first();

                    if (!$areaOlimpiada) {
                        $errorMsg = "Relación {$index}: El área {$relacion['id_area']} no está asociada a la olimpiada actual ({$olimpiadaActual->gestion})";
                        $errors[] = $errorMsg;
                        Log::warning("[SERVICE] {$errorMsg}");
                        continue;
                    }

                    $existing = AreaNivel::where('id_area', $relacion['id_area'])
                        ->where('id_nivel', $relacion['id_nivel'])
                        ->where('id_grado_escolaridad', $relacion['id_grado_escolaridad'])
                        ->where('id_olimpiada', $olimpiadaActual->id_olimpiada)
                        ->first();

                    if ($existing) {
                        $errorMsg = "Relación {$index}: Ya existe esta combinación exacta de área, nivel y grado para la gestión actual";
                        $errors[] = $errorMsg;
                        Log::warning("[SERVICE] {$errorMsg}");
                        continue;
                    }

                    $areaNivel = AreaNivel::create([
                        'id_area' => $relacion['id_area'],
                        'id_nivel' => $relacion['id_nivel'],
                        'id_grado_escolaridad' => $relacion['id_grado_escolaridad'],
                        'id_olimpiada' => $olimpiadaActual->id_olimpiada,
                        'activo' => $relacion['activo']
                    ]);
                    
                    $inserted[] = $areaNivel;
                    Log::info("[SERVICE] Relación {$index} creada exitosamente:", [
                        'id_area_nivel' => $areaNivel->id_area_nivel,
                        'combinacion' => "Área: {$relacion['id_area']}, Nivel: {$relacion['id_nivel']}, Grado: {$relacion['id_grado_escolaridad']}"
                    ]);

                } catch (\Exception $e) {
                    $errorMsg = "Relación {$index}: Error inesperado - " . $e->getMessage();
                    $errors[] = $errorMsg;
                    Log::error("[SERVICE] {$errorMsg}");
                }
            }

            $message = '';
        if (count($inserted) > 0) {
            $areaId = $inserted[0]->id_area;
            $area = Area::find($areaId);
            $nombreArea = $area ? $area->nombre : 'el área seleccionada';
            
            $message = "Los niveles y grados fueron asignados correctamente al área {$nombreArea}";
        }
        
        if (count($errors) > 0) {
            if (count($inserted) > 0) {
                $message .= ". Se encontraron algunos errores en " . count($errors) . " relaciones.";
            } else {
                $message = "❌ Ninguna de las {$totalRelations} relaciones pudo ser procesada.";
            }
        }

            $result = [
                'area_niveles' => $inserted,
                'olimpiada' => $olimpiadaActual->gestion,
                'message' => $message,
                'errors' => $errors,
                'success_count' => count($inserted),
                'error_count' => count($errors),
                'distribucion' => $distribucion ?? []
            ];

            Log::info('[SERVICE] Resultado final:', $result);
            return $result;

        } catch (\Exception $e) {
            Log::error('[SERVICE] Error general en createMultipleAreaNivel:', [
                'exception' => $e->getMessage()
            ]);
            throw new \Exception("Error al procesar relaciones: " . $e->getMessage());
        }
    }

    public function updateAreaNivelByArea(int $id_area, array $niveles): array
    {
        $olimpiadaActual = $this->obtenerOlimpiadaActual();
        $updatedNiveles = [];
    
        foreach ($niveles as $nivelData) {
            // Verificar si ya existe la relación
            $areaNivel = AreaNivel::where('id_area', $id_area)
                ->where('id_nivel', $nivelData['id_nivel'])
                ->where('id_grado_escolaridad', $nivelData['id_grado_escolaridad'])
                ->where('id_olimpiada', $olimpiadaActual->id_olimpiada)
                ->first();

            if ($areaNivel) {
                $areaNivel->update(['activo' => $nivelData['activo']]);
                $updatedNiveles[] = $areaNivel;
            } else {
                $newAreaNivel = AreaNivel::create([
                    'id_area' => $id_area,
                    'id_nivel' => $nivelData['id_nivel'],
                    'id_grado_escolaridad' => $nivelData['id_grado_escolaridad'],
                    'id_olimpiada' => $olimpiadaActual->id_olimpiada,
                    'activo' => $nivelData['activo']
                ]);
                $updatedNiveles[] = $newAreaNivel;
            }
        }

        return [
            'area_niveles' => $updatedNiveles,
            'olimpiada' => $olimpiadaActual->gestion,
            'message' => 'Relaciones área-nivel actualizadas exitosamente para la gestión actual'
        ];
    }

    public function updateAreaNivel(int $id, array $data): array
    {
        $areaNivel = AreaNivel::find($id);
        
        if (!$areaNivel) {
            throw new \Exception('Relación área-nivel no encontrada');
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
            throw new \Exception('Relación área-nivel no encontrada');
        }

        $areaNivel->delete();

        return [
            'message' => 'Relación área-nivel eliminada exitosamente'
        ];
    }

    public function getAreasConNivelesSimplificado(): array
    {
        $olimpiadaActual = $this->obtenerOlimpiadaActual();
        $areas = Area::with([
            'areaNiveles' => function($query) use ($olimpiadaActual) {
                $query->where('id_olimpiada', $olimpiadaActual->id_olimpiada)
                      ->where('activo', true);
            },
            'areaNiveles.nivel:id_nivel,nombre',
            'areaNiveles.gradoEscolaridad:id_grado_escolaridad,nombre'
        ])
        ->whereHas('areaNiveles', function($query) use ($olimpiadaActual) {
            $query->where('id_olimpiada', $olimpiadaActual->id_olimpiada)
                  ->where('activo', true);
        })
        ->get(['id_area', 'nombre']);

        $resultado = $areas->map(function($area) {
            $nivelesAgrupados = $area->areaNiveles->groupBy('id_nivel')->map(function($areaNivelesPorNivel) {
                $primerNivel = $areaNivelesPorNivel->first();
                $grados = $areaNivelesPorNivel->map(function($areaNivel) {
                    return [
                        'id_grado_escolaridad' => $areaNivel->gradoEscolaridad->id_grado_escolaridad,
                        'nombre_grado' => $areaNivel->gradoEscolaridad->nombre
                    ];
                });

                return [
                    'id_nivel' => $primerNivel->nivel->id_nivel,
                    'nombre_nivel' => $primerNivel->nivel->nombre,
                    'grados' => $grados->values()
                ];
            });

            return [
                'id_area' => $area->id_area,
                'nombre' => $area->nombre,
                'niveles' => $nivelesAgrupados->values()
            ];
        });

        return [
            'areas' => $resultado->values(),
            'olimpiada_actual' => $olimpiadaActual->gestion,
            'message' => 'Áreas con niveles y grados activos obtenidas exitosamente'
        ];
    }

    public function getAreasConNivelesPorOlimpiada(int $idOlimpiada): array
    {
        $olimpiada = Olimpiada::findOrFail($idOlimpiada);
        $areas = Area::with([
            'areaNiveles' => function($query) use ($idOlimpiada) {
                $query->where('id_olimpiada', $idOlimpiada)
                      ->where('activo', true);
            },
            'areaNiveles.nivel:id_nivel,nombre',
            'areaNiveles.gradoEscolaridad:id_grado_escolaridad,nombre'
        ])
        ->whereHas('areaNiveles', function($query) use ($idOlimpiada) {
            $query->where('id_olimpiada', $idOlimpiada)
                  ->where('activo', true);
        })
        ->get(['id_area', 'nombre']);

        $resultado = $areas->map(function($area) {
            $nivelesAgrupados = $area->areaNiveles->groupBy('id_nivel')->map(function($areaNivelesPorNivel) {
                $primerNivel = $areaNivelesPorNivel->first();
                $grados = $areaNivelesPorNivel->map(function($areaNivel) {
                    return [
                        'id_grado_escolaridad' => $areaNivel->gradoEscolaridad->id_grado_escolaridad,
                        'nombre_grado' => $areaNivel->gradoEscolaridad->nombre
                    ];
                });

                return [
                    'id_nivel' => $primerNivel->nivel->id_nivel,
                    'nombre_nivel' => $primerNivel->nivel->nombre,
                    'grados' => $grados->values()
                ];
            });

            return [
                'id_area' => $area->id_area,
                'nombre' => $area->nombre,
                'niveles' => $nivelesAgrupados->values()
            ];
        });

        return [
            'areas' => $resultado->values(),
            'olimpiada' => $olimpiada->gestion,
            'message' => "Áreas con niveles y grados activos obtenidas para la gestión {$olimpiada->gestion}"
        ];
    }

    public function getAreasConNivelesPorGestion(string $gestion): array
    {
        $olimpiada = Olimpiada::where('gestion', $gestion)->firstOrFail();
        return $this->getAreasConNivelesPorOlimpiada($olimpiada->id_olimpiada);
    }

    public function getAllAreaNivelWithDetails(): array
    {
    $areaNiveles = AreaNivel::with([
        'area:id_area,nombre',
        'nivel:id_nivel,nombre',
        'gradoEscolaridad:id_grado_escolaridad,nombre',
        'olimpiada:id_olimpiada,gestion'
    ])->get();

    return [
        'area_niveles' => $areaNiveles,
        'message' => 'Todas las relaciones área-nivel obtenidas con detalles'
    ];
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

            $areaNiveles = AreaNivel::with([
                'nivel:id_nivel,nombre',
                'gradoEscolaridad:id_grado_escolaridad,nombre'
            ])
            ->where('id_area', $id_area)
            ->where('id_olimpiada', $olimpiada->id_olimpiada)
            ->where('activo', true)
            ->get();

            $nivelesGrados = $areaNiveles->map(function($areaNivel) {
                return [
                    'id_area_nivel' => $areaNivel->id_area_nivel,
                    'nivel' => [
                        'id_nivel' => $areaNivel->nivel->id_nivel,
                        'nombre' => $areaNivel->nivel->nombre
                    ],
                    'grado_escolaridad' => [
                        'id_grado_escolaridad' => $areaNivel->gradoEscolaridad->id_grado_escolaridad,
                        'nombre' => $areaNivel->gradoEscolaridad->nombre
                    ],
                    'activo' => $areaNivel->activo
                ];
            });

            $nivelesAgrupados = $areaNiveles->groupBy('id_nivel')->map(function($areaNivelesPorNivel) {
                $primerNivel = $areaNivelesPorNivel->first();
                $grados = $areaNivelesPorNivel->map(function($areaNivel) {
                    return [
                        'id_grado_escolaridad' => $areaNivel->gradoEscolaridad->id_grado_escolaridad,
                        'nombre' => $areaNivel->gradoEscolaridad->nombre
                    ];
                });

                return [
                    'id_nivel' => $primerNivel->nivel->id_nivel,
                    'nombre_nivel' => $primerNivel->nivel->nombre,
                    'grados' => $grados->values()
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
                    'niveles_individuales' => $nivelesGrados,
                    'niveles_con_grados_agrupados' => $nivelesAgrupados->values(),
                    'total_relaciones' => $areaNiveles->count(),
                    'total_niveles' => $nivelesAgrupados->count()
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

    public function getNivelesGradosByAreasAndGestion(array $id_areas, string $gestion): array
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

            $areas = Area::whereIn('id_area', $id_areas)->get();
            if ($areas->isEmpty()) {
                return [
                    'success' => false,
                    'data' => [],
                    'message' => "No se encontraron las áreas con los IDs proporcionados"
                ];
            }

            $areaNiveles = AreaNivel::with([
                'area:id_area,nombre',
                'nivel:id_nivel,nombre',
                'gradoEscolaridad:id_grado_escolaridad,nombre'
            ])
            ->whereIn('id_area', $id_areas)
            ->where('id_olimpiada', $olimpiada->id_olimpiada)
            ->where('activo', true)
            ->get();

            $resultadoPorArea = $areas->map(function($area) use ($areaNiveles, $olimpiada) {
                $relacionesArea = $areaNiveles->where('id_area', $area->id_area);
                
                $nivelesAgrupados = $relacionesArea->groupBy('id_nivel')->map(function($areaNivelesPorNivel) {
                    $primerNivel = $areaNivelesPorNivel->first();
                    $grados = $areaNivelesPorNivel->map(function($areaNivel) {
                        return [
                            'id_grado_escolaridad' => $areaNivel->gradoEscolaridad->id_grado_escolaridad,
                            'nombre' => $areaNivel->gradoEscolaridad->nombre
                        ];
                    });

                    return [
                        'id_nivel' => $primerNivel->nivel->id_nivel,
                        'nombre_nivel' => $primerNivel->nivel->nombre,
                        'grados' => $grados->values()
                    ];
                });

                return [
                    'area' => [
                        'id_area' => $area->id_area,
                        'nombre' => $area->nombre
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
                        'gestion' => $olimpiada->gestion,
                        'nombre' => $olimpiada->nombre
                    ],
                    'areas' => $resultadoPorArea->values(),
                    'total_areas' => $areas->count(),
                    'total_relaciones' => $areaNiveles->count()
                ],
                'message' => "Niveles y grados obtenidos exitosamente para {$areas->count()} áreas en la gestión {$gestion}"
            ];

        } catch (\Exception $e) {
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
        $areas = $this->areaNivelRepository->getActualesByOlimpiada($olimpiadaActual->id_olimpiada);

        $resultado = $areas->map(function($area) {
            $niveles = $area->areaNiveles->map(function($areaNivel) {
                return [
                    'id_area_nivel' => $areaNivel->id_area_nivel,
                    'id_nivel' => $areaNivel->id_nivel,
                    'nombre' => $areaNivel->nivel->nombre
                ];
            });

            return [
                'id_area' => $area->id_area,
                'area' => $area->nombre,
                'niveles' => $niveles->values()
            ];
        });

        return $resultado->values()->all();
    }
}