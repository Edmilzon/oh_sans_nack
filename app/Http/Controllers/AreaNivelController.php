<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Fase;
use App\Services\AreaNivelService;
use App\Services\OlimpiadaService;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class AreaNivelController extends Controller
{
    protected $areaNivelService;

    public function __construct(AreaNivelService $areaNivelService)
    {
        $this->areaNivelService = $areaNivelService;
    }

    public function index(): JsonResponse
    {
        try {
            $areaNiveles = $this->areaNivelService->getAreaNivelList();
            return response()->json([
                'success' => true,
                'data' => $areaNiveles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las relaciones área-nivel: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function store(Request $request): JsonResponse
    {
        try {
            Log::info('[CONTROLLER] Request recibido en store:', $request->all());

            $validatedData = $request->validate([
                '*.id_area' => 'required|integer|exists:area,id_area',
                '*.id_nivel' => 'required|integer|exists:nivel,id_nivel',
                '*.id_grado_escolaridad' => 'required|integer|exists:grado_escolaridad,id_grado_escolaridad',
                '*.activo' => 'required|boolean'
            ]);

            $result = $this->areaNivelService->createMultipleAreaNivel($validatedData);
            
            $response = [
                'success' => true,
                'data' => $result['area_niveles'],
                'message' => $result['message'],
                'olimpiada_actual' => $result['olimpiada'],
                'success_count' => $result['success_count'],
                'created_count' => count($result['area_niveles'])
            ];

            if (!empty($result['errors'])) {
                $response['errors'] = $result['errors'];
                $response['error_count'] = $result['error_count'];
            }

            if (!empty($result['distribucion'])) {
                $response['distribucion'] = $result['distribucion'];
            }

            return response()->json($response, 201);
            
        } catch (ValidationException $e) {
            Log::error('[CONTROLLER] Error de validación:', $e->errors());
            
            return response()->json([
                'success' => false,
                'message' => 'Error de validación en los datos',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('[CONTROLLER] Error general en store:', $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear las relaciones área-nivel: ' . $e->getMessage()
            ], 400);
        }
    }

    public function getByArea($id_area): JsonResponse
    {
        try {
            $areaNiveles = $this->areaNivelService->getAreaNivelByArea($id_area);
            return response()->json([
                'success' => true,
                'data' => $areaNiveles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las relaciones área-nivel: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getByAreaAll($id_area): JsonResponse
    {
        try {
            $areaNiveles = $this->areaNivelService->getAreaNivelByAreaAll($id_area);
            return response()->json([
                'success' => true,
                'data' => $areaNiveles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las relaciones área-nivel a detalle: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAreasConNiveles(): JsonResponse
    {
        try {
            $result = $this->areaNivelService->getAreaNivelesAsignadosAll();
    
            return response()->json([
                'success' => true,
                'data' => $result['areas'],
                'olimpiada_actual' => $result['olimpiada_actual'],
                'message' => $result['message']
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener áreas con niveles: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAreasConNivelesSimplificado(): JsonResponse
    {
        try {
            $result = $this->areaNivelService->getAreasConNivelesSimplificado();

            return response()->json([
                'success' => true,
                'data' => $result['areas'],
                'olimpiada_actual' => $result['olimpiada_actual'],
                'message' => $result['message']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener áreas con niveles: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAreasConNivelesPorOlimpiada(int $id_olimpiada): JsonResponse
    {
        try {
            $result = $this->areaNivelService->getAreasConNivelesPorOlimpiada($id_olimpiada);

            return response()->json([
                'success' => true,
                'data' => $result['areas'],
                'olimpiada' => $result['olimpiada'],
                'message' => $result['message']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener áreas con niveles: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAreasConNivelesPorGestion(string $gestion): JsonResponse
    {
        try {
            $result = $this->areaNivelService->getAreasConNivelesPorGestion($gestion);

            return response()->json([
                'success' => true,
                'data' => $result['areas'],
                'olimpiada' => $result['olimpiada'],
                'message' => $result['message']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener áreas con niveles: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $result = $this->areaNivelService->getAreaNivelById($id);
            
            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Relación área-nivel no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $result['area_nivel'],
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la relación área-nivel: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateByArea($id_area, Request $request): JsonResponse
    {
        try {
           
            $validatedData = $request->validate([
                '*.id_nivel' => 'required|integer|exists:nivel,id_nivel',
                '*.id_grado_escolaridad' => 'required|integer|exists:grado_escolaridad,id_grado_escolaridad',
                '*.activo' => 'required|boolean'
            ]);

            $result = $this->areaNivelService->updateAreaNivelByArea($id_area, $validatedData);
            
            return response()->json([
                'success' => true,
                'data' => $result['area_niveles'],
                'olimpiada_actual' => $result['olimpiada'],
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar las relaciones área-nivel: ' . $e->getMessage()
            ], 400);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {

            $validatedData = $request->validate([
                'id_area' => 'sometimes|required|integer|exists:area,id_area',
                'id_nivel' => 'sometimes|required|integer|exists:nivel,id_nivel',
                'id_grado_escolaridad' => 'sometimes|required|integer|exists:grado_escolaridad,id_grado_escolaridad',
                'activo' => 'sometimes|required|boolean'
            ]);

            $result = $this->areaNivelService->updateAreaNivel($id, $validatedData);
            
            return response()->json([
                'success' => true,
                'data' => $result['area_nivel'],
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la relación área-nivel: ' . $e->getMessage()
            ], 400);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {

            $existeFase = \App\Model\Fase::exists();
        
            if ($existeFase) {
            return response()->json([
                'success' => false,
                'message' => 'Se está en una fase de evaluación, por lo tanto no se pueden modificar los datos'
            ], 422);
            }

            $result = $this->areaNivelService->deleteAreaNivel($id);
            
            return response()->json([
                'success' => true,
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la relación área-nivel: ' . $e->getMessage()
            ], 400);
        }
    }

    public function getAllWithDetails(): JsonResponse
    {
    try {
        $result = $this->areaNivelService->getAllAreaNivelWithDetails();

        return response()->json([
            'success' => true,
            'data' => $result['area_niveles'],
            'message' => $result['message']
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al obtener las relaciones área-nivel: ' . $e->getMessage()
        ], 500);
    }
    }

    public function getByGestionAndAreas(Request $request): JsonResponse
    {
    try {
        $request->validate([
            'gestion' => 'required|string',
            'id_areas' => 'required|array',
            'id_areas.*' => 'integer|exists:area,id_area'
        ]);

        $gestion = $request->input('gestion');
        $idAreas = $request->input('id_areas');

        $result = $this->areaNivelService->getAreaNivelByGestionAndAreas($gestion, $idAreas);

        return response()->json([
            'success' => true,
            'data' => $result['area_niveles'],
            'olimpiada' => $result['olimpiada'],
            'message' => $result['message']
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al obtener las relaciones área-nivel: ' . $e->getMessage()
        ], 500);
    }
    }

     public function getNivelesGradosByAreaAndGestion(string $gestion, int $id_area): JsonResponse
    {
        try {
            $result = $this->areaNivelService->getNivelesGradosByAreaAndGestion($id_area, $gestion);

            $status = $result['success'] ? 200 : 404;

            return response()->json($result, $status);

        } catch (\Exception $e) {
            Log::error('[CONTROLLER] Error en getNivelesGradosByAreaAndGestion:', [
                'gestion' => $gestion,
                'id_area' => $id_area,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Error al obtener los niveles y grados: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getNivelesGradosByAreasAndGestion(Request $request, string $gestion): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'id_areas' => 'required|array',
                'id_areas.*' => 'integer|exists:area,id_area'
            ]);

            $result = $this->areaNivelService->getNivelesGradosByAreasAndGestion(
                $validatedData['id_areas'],
                $gestion
            );

            $status = $result['success'] ? 200 : 404;

            return response()->json($result, $status);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('[CONTROLLER] Error en getNivelesGradosByAreasAndGestion:', [
                'gestion' => $gestion,
                'id_areas' => $request->input('id_areas', []),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Error al obtener los niveles y grados: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getActuales(): JsonResponse
    {
        try {
            $result = $this->areaNivelService->getAreaNivelActuales();

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('[CONTROLLER] Error en getActuales:', [
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'message' => 'Error al obtener las relaciones área-nivel actuales: ' . $e->getMessage()
            ], 500);
        }
    }
}