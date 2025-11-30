<?php

namespace App\Services;

use App\Repositories\ParametroRepository;
use App\Repositories\AreaNivelRepository;
use App\Services\OlimpiadaService;
use Illuminate\Database\Eloquent\Collection;

class ParametroService
{
    protected $parametroRepository;
    protected $areaNivelRepository;
    protected $olimpiadaService;

    const MAXIMO_CLASIFICADOS = PHP_INT_MAX;

    public function __construct(
        ParametroRepository $parametroRepository,
        AreaNivelRepository $areaNivelRepository,
        OlimpiadaService $olimpiadaService
    ) {
        $this->parametroRepository = $parametroRepository;
        $this->areaNivelRepository = $areaNivelRepository;
        $this->olimpiadaService = $olimpiadaService;
    }

    public function getAllParametros(): array
    {
        $parametros = $this->parametroRepository->getAll();

        $formatted = $parametros->map(function($parametro) {
            return $this->formatParametro($parametro);
        });

        return [
            'parametros' => $formatted,
            'total' => $parametros->count(),
            'message' => 'Parámetros obtenidos exitosamente'
        ];
    }

    public function getParametrosByOlimpiada(int $idOlimpiada): array
    {
        $parametros = $this->parametroRepository->getByOlimpiada($idOlimpiada);

        $formatted = $parametros->map(function($parametro) {
            return $this->formatParametro($parametro);
        });

        return [
            'parametros' => $formatted,
            'total' => $parametros->count(),
            'message' => "Parámetros obtenidos para la olimpiada {$idOlimpiada}"
        ];
    }

    public function createOrUpdateParametros(array $data): array
    {
        $results = [];
        $errors = [];

        foreach ($data['area_niveles'] as $areaNivelData) {
            try {
                $areaNivel = $this->areaNivelRepository->getById($areaNivelData['id_area_nivel']);
                
                if (!$areaNivel) {
                    $errors[] = "El área-nivel con ID {$areaNivelData['id_area_nivel']} no existe";
                    continue;
                }

                $cantidadMaxApro = isset($areaNivelData['cantidad_max_apro']) 
                    ? $areaNivelData['cantidad_max_apro'] 
                    : null;

                $parametro = $this->parametroRepository->updateOrCreateByAreaNivel(
                    $areaNivelData['id_area_nivel'],
                    [
                        'nota_min_clasif' => $areaNivelData['nota_min_clasif'],
                        'cantidad_max_apro' => $cantidadMaxApro
                    ]
                );

                $results[] = $this->formatParametro($parametro);

            } catch (\Exception $e) {
                $errors[] = "Error procesando área-nivel {$areaNivelData['id_area_nivel']}: " . $e->getMessage();
            }
        }

        $response = [
            'parametros_actualizados' => $results,
            'total_procesados' => count($results),
            'message' => count($results) . ' parámetros procesados exitosamente'
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
            $response['message'] .= ' con ' . count($errors) . ' errores';
        }

        return $response;
    }

    public function createOrUpdateParametro(array $data): array
    {
        $areaNivel = $this->areaNivelRepository->getById($data['id_area_nivel']);
        
        if (!$areaNivel) {
            throw new \Exception("El área-nivel con ID {$data['id_area_nivel']} no existe");
        }

        $cantidadMaxApro = isset($data['cantidad_max_apro']) 
            ? $data['cantidad_max_apro'] 
            : null;
        
        $parametro = $this->parametroRepository->updateOrCreateByAreaNivel(
            $data['id_area_nivel'],
            [
                'nota_min_clasif' => $data['nota_min_clasif'],
                'cantidad_max_apro' => $cantidadMaxApro
            ]
        );

        return [
            'parametro' => $this->formatParametro($parametro),
            'message' => 'Parámetro guardado exitosamente'
        ];
    }

    public function getAllParametrosByGestiones(): array
    {
        $parametros = $this->parametroRepository->getAllParametrosByGestiones();

        $olimpiadaActual = $this->olimpiadaService->obtenerOlimpiadaActual();
        $gestionActual = $olimpiadaActual->gestion;

        $parametrosPorGestion = $parametros->groupBy('id_olimpiada');

        $resultado = [];

        foreach ($parametrosPorGestion as $idOlimpiada => $parametrosGestion) {
            $gestion = $parametrosGestion->first()->gestion;

            if ($gestion == $gestionActual) {
                continue;
            }

            $parametrosFormateados = $parametrosGestion->map(function($parametro) {
                $cantMaxClasificados = $parametro->cant_max_clasificados ?? self::MAXIMO_CLASIFICADOS;

                return [
                    'id_area_nivel' => $parametro->id_area_nivel,
                    'nombre_area' => $parametro->nombre_area,
                    'nombre_nivel' => $parametro->nombre_nivel,
                    'nota_minima' => $parametro->nota_minima,
                    'cant_max_clasificados' => $cantMaxClasificados
                ];
            });

            $resultado[] = [
                'id_olimpiada' => $idOlimpiada,
                'gestion' => $gestion,
                'parametros' => $parametrosFormateados,
                'total_parametros' => $parametrosFormateados->count()
            ];
        }

        usort($resultado, function($a, $b) {
            return $b['gestion'] - $a['gestion'];
        });

        return [
            'gestiones' => $resultado,
            'total_gestiones' => count($resultado),
            'message' => 'Parámetros de todas las gestiones obtenidos exitosamente (excluyendo la gestión actual)'
        ];
    }

    private function formatParametro($parametro): array
    {
        $cantidadMaxApro = $parametro->cantidad_max_apro ?? self::MAXIMO_CLASIFICADOS;

        return [
            'id_parametro' => $parametro->id_parametro,
            'nota_min_clasif' => $parametro->nota_min_clasif,
            'cantidad_max_apro' => $cantidadMaxApro,
            'area_nivel' => [
                'id_area_nivel' => $parametro->areaNivel->id_area_nivel,
                'area' => [
                    'id_area' => $parametro->areaNivel->area->id_area,
                    'nombre' => $parametro->areaNivel->area->nombre
                ],
                'nivel' => [
                    'id_nivel' => $parametro->areaNivel->nivel->id_nivel,
                    'nombre' => $parametro->areaNivel->nivel->nombre
                ],
                'olimpiada' => [
                    'id_olimpiada' => $parametro->areaNivel->olimpiada->id_olimpiada,
                    'gestion' => $parametro->areaNivel->olimpiada->gestion,
                    'nombre' => $parametro->areaNivel->olimpiada->nombre
                ]
            ]
        ];
    }

    public function getParametrosByAreaNiveles(array $idsAreaNivel): array
    {
        $parametros = $this->parametroRepository->getParametrosByAreaNiveles($idsAreaNivel);

        if ($parametros->isEmpty()) {
            return [
                'areas_nivel' => $idsAreaNivel,
                'parametros' => [],
                'total_areas' => 0,
                'message' => 'No se encontraron parámetros para los áreas-nivel especificados'
            ];
        }

        $parametrosPorAreaNivel = $parametros->groupBy('id_area_nivel');

        $resultado = [];

        foreach ($parametrosPorAreaNivel as $idAreaNivel => $parametrosArea) {
            $primero = $parametrosArea->first();

            $parametrosFormateados = $parametrosArea->map(function($parametro) {
                $cantMaxClasificados = $parametro->cant_max_clasificados ?? self::MAXIMO_CLASIFICADOS;

                return [
                    'id_olimpiada' => $parametro->id_olimpiada,
                    'gestion' => $parametro->gestion,
                    'nota_minima' => $parametro->nota_minima,
                    'cant_max_clasificados' => $cantMaxClasificados
                ];
            });

            $resultado[] = [
                'area_nivel' => [
                    'id_area_nivel' => $idAreaNivel,
                    'nombre_area' => $primero->nombre_area,
                    'nombre_nivel' => $primero->nombre_nivel
                ],
                'parametros' => $parametrosFormateados,
                'total_gestiones' => $parametrosArea->count()
            ];
        }

        return [
            'areas_nivel' => $resultado,
            'total_areas' => count($resultado),
            'message' => 'Parámetros históricos obtenidos para ' . count($idsAreaNivel) . ' áreas-nivel'
        ];
    }
}