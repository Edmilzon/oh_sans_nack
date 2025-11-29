<?php

namespace App\Http\Controllers;

use App\Services\AreaOlimpiadaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

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

            $olimpiada = \App\Model\Olimpiada::where('gestion', $gestion)->first();
            $mensajeFase = '';
        
            if ($olimpiada) {
            $existeFaseEnGestion = \App\Model\Fase::whereHas('areaNivel', function($query) use ($olimpiada) {
                $query->where('id_olimpiada', $olimpiada->id_olimpiada);
            })->exists();
            
            if ($existeFaseEnGestion) {
                $mensajeFase = 'La funcionalidad de asignar niveles a un Área no está disponible porque el proceso de evaluación ha iniciado. Solo puede ver las asignaciones previamente realizadas.';
            } else {
                $mensajeFase = 'No existe un proceso de evaluación.';
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
