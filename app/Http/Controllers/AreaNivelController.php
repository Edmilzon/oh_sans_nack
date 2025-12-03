<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\AreaNivelService;
use Illuminate\Routing\Controller;

class AreaNivelController extends Controller
{
    protected $areaNivelService;

    public function __construct(AreaNivelService $areaNivelService)
    {
        $this->areaNivelService = $areaNivelService;
    }

    // ✅ MÉTODOS que SOLO trabajan con area_nivel (sin grados)

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

    // GET /api/area-nivel/actuales
    public function getActuales(): JsonResponse
    {
        try {
            $data = $this->areaNivelService->getAreasNivelesGestionActual();

            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener áreas y niveles actuales.',
                'error'   => $e->getMessage()
            ], 500);
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

    public function getNivelesPorAreaOlimpiada($idOlimpiada, $idArea): JsonResponse
    {
        // Buscamos la relación AreaOlimpiada específica
        $areaOlimpiada = \App\Model\AreaOlimpiada::where('id_olimpiada', $idOlimpiada)
            ->where('id_area', $idArea)
            ->first();

        if (!$areaOlimpiada) {
            return response()->json(['success' => true, 'message' => 'No hay niveles', 'data' => []]);
        }

        // Obtenemos los niveles asociados
        $niveles = \App\Model\AreaNivel::with('nivel')
            ->where('id_area_olimpiada', $areaOlimpiada->id_area_olimpiada)
            ->get()
            ->map(function($an) {
                return [
                    'id_nivel' => $an->nivel->id_nivel,
                    'nombre'   => $an->nivel->nombre
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Niveles del área obtenidos correctamente',
            'data'    => $niveles
        ]);
    }
}
