<?php

namespace App\Http\Controllers;

use App\Services\OlimpiadaService;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class OlimpiadaController extends Controller
{
    public function __construct(
        protected OlimpiadaService $olimpiadaService
    ) {}

    public function olimpiadasAnteriores(): JsonResponse
    {
        try {
            $olimpiadas = $this->olimpiadaService->obtenerOlimpiadasAnteriores();

            return response()->json([
                'success' => true,
                'data' => $olimpiadas,
                'message' => 'Olimpiadas anteriores obtenidas correctamente'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las olimpiadas anteriores: ' . $e->getMessage()
            ], 500);
        }
    }

    public function olimpiadaActual(): JsonResponse
    {
        try {
            $olimpiada = $this->olimpiadaService->obtenerOlimpiadaActual();

            return response()->json([
                'success' => true,
                'data' => $olimpiada,
                'message' => 'Olimpiada actual obtenida correctamente'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la olimpiada actual: ' . $e->getMessage()
            ], 500);
        }
    }

    public function gestiones(): JsonResponse
    {
        try {
            $gestiones = $this->olimpiadaService->obtenerGestiones();

            return response()->json([
                'success' => true,
                'data' => $gestiones,
                'message' => 'Gestiones obtenidas correctamente'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las gestiones: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255',
                'gestion' => 'required|string|size:4', 
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Revise si ingreso valores válidos para gestion y nombre',
                    'errors' => $validator->errors()
                ], 422);
            }

            $olimpiada = $this->olimpiadaService->crearOlimpiada([
                'nombre' => $request->nombre,
                'gestion' => $request->gestion,
            ]);

            return response()->json([
                'success' => true,
                'data' => $olimpiada,
                'message' => 'Olimpiada creada correctamente'
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la olimpiada: ' . $e->getMessage()
            ], 500);
        }
    }

    public function activar(Request $request, int $id): JsonResponse
    {
        try {
            $olimpiada = $this->olimpiadaService->obtenerOlimpiadaPorId($id);
            
            if (!$olimpiada) {
                return response()->json([
                    'success' => false,
                    'message' => 'Olimpiada no encontrada'
                ], 404);
            }

            $this->olimpiadaService->activarOlimpiada($id);

            $olimpiadaActualizada = $this->olimpiadaService->obtenerOlimpiadaPorId($id);

            return response()->json([
                'success' => true,
                'data' => $olimpiadaActualizada,
                'message' => 'Se escogió la olimpiada exitosamente.'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al escoger la olimpiada: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index(): JsonResponse
    {
        try {
            $olimpiadas = $this->olimpiadaService->obtenerGestiones();

            return response()->json([
                'success' => true,
                'data' => $olimpiadas,
                'message' => 'Olimpiadas obtenidas correctamente'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las olimpiadas: ' . $e->getMessage()
            ], 500);
        }
    }
}
