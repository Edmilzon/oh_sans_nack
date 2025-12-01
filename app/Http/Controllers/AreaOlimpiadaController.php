<?php

namespace App\Http\Controllers;

use App\Services\AreaOlimpiadaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Model\Olimpiada;
use App\Model\FaseGlobal;

class AreaOlimpiadaController extends Controller
{
    protected $areaOlimpiadaService;

    public function __construct(AreaOlimpiadaService $areaOlimpiadaService)
    {
        $this->areaOlimpiadaService = $areaOlimpiadaService;
    }

    /**
     * Obtiene todas las áreas asociadas a una olimpiada.
     *
     * @param int|string $identifier
     * @return JsonResponse
     */
    public function getAreasByOlimpiada(int $identifier): JsonResponse
    {
        try {
            $areas = $this->areaOlimpiadaService->getAreasByOlimpiada($identifier);

            return response()->json([
                'message' => 'Áreas obtenidas exitosamente para la olimpiada.',
                'data' => $areas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las áreas de la olimpiada.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAreasGestionActual(): JsonResponse
    {
        try {
            $areas = $this->areaOlimpiadaService->getAreasGestionActual();
            
            return response()->json([
                'success' => true,
                'data' => $areas
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las áreas: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getNombresAreasGestionActual(): JsonResponse
    {
        try {
            $nombresAreas = $this->areaOlimpiadaService->getNombresAreasGestionActual();
            
            return response()->json([
                'success' => true,
                'data' => $nombresAreas
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los nombres de las áreas: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getAreasByGestion(string $gestion): JsonResponse
    {
        try {
            $areas = $this->areaOlimpiadaService->getAreasByGestion($gestion);

            $olimpiada = Olimpiada::where('gestion', $gestion)->first();
            $mensajeFase = '';
        
            if ($olimpiada) {
                // Buscar fases globales de evaluación o calificación con cronograma activo
                $faseActiva = FaseGlobal::where('id_olimpiada', $olimpiada->id_olimpiada)
                    ->where(function($query) {
                        // Buscar fases que sean de evaluación o calificación
                        $query->where('nombre', 'like', '%Evaluación%')
                              ->orWhere('nombre', 'like', '%Calificación%')
                              ->orWhere('nombre', 'like', '%evaluación%')
                              ->orWhere('nombre', 'like', '%calificación%');
                    })
                    ->whereHas('cronogramas', function($query) {
                        // Verificar que el cronograma esté activo (estado = true)
                        $query->where('estado', true);
                    })
                    ->first();
                
                if ($faseActiva) {
                    $mensajeFase = 'La funcionalidad de asignar niveles a un Área no está disponible porque el proceso de evaluación ha iniciado. Solo puede ver las asignaciones previamente realizadas.';
                } else {
                    // Verificar si hay alguna fase global activa (para mostrar mensaje apropiado)
                    $faseGlobalActiva = FaseGlobal::where('id_olimpiada', $olimpiada->id_olimpiada)
                        ->whereHas('cronogramas', function($query) {
                            $query->where('estado', true);
                        })
                        ->first();
                    
                    if ($faseGlobalActiva) {
                        // Si hay fase activa pero no es de evaluación/calificación (puede ser configuración, clasificación, premiación)
                        $mensajeFase = 'No existe un proceso de evaluación activo.';
                    } else {
                        // No hay ninguna fase activa
                        $mensajeFase = 'No existe un proceso de evaluación.';
                    }
                }
            } else {
                $mensajeFase = 'No se encontró la olimpiada para la gestión proporcionada.';
            }

            return response()->json([
                'success' => true,
                'message' => $mensajeFase,
                'data' => $areas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Error al obtener las áreas para la gestión {$gestion}",
                'error' => $e->getMessage()
            ], 500);
        }
    }
}