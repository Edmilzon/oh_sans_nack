<?php

namespace App\Http\Controllers;

use App\Services\RolAccionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class RolAccionController extends Controller
{
    protected $rolAccionService;

    public function __construct(RolAccionService $rolAccionService)
    {
        $this->rolAccionService = $rolAccionService;
    }

    /**
     * Obtener acciones asignadas a un rol.
     */
    public function index(int $idRol): JsonResponse
    {
        try {
            $acciones = $this->rolAccionService->obtenerAccionesPorRol($idRol);

            return response()->json([
                'success' => true,
                'data' => $acciones,
                'message' => 'Acciones del rol obtenidas correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener acciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Asignar múltiples acciones a un rol.
     */
    public function store(Request $request, int $idRol): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'acciones_ids' => 'required|array|min:1',
            'acciones_ids.*' => 'integer|exists:accion_sistema,id_accion'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $resultado = $this->rolAccionService->sincronizarAcciones($idRol, $request->input('acciones_ids'));

            return response()->json([
                'success' => true,
                'message' => 'Acciones asignadas correctamente al rol',
                'data' => $resultado
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar acciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revocar una acción específica de un rol.
     */
    public function destroy(int $idRol, int $idAccion): JsonResponse
    {
        try {
            $eliminado = $this->rolAccionService->revocarAccion($idRol, $idAccion);

            if (!$eliminado) {
                return response()->json([
                    'success' => false,
                    'message' => 'La asignación no existía o no se pudo eliminar'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Acción revocada correctamente del rol'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al revocar acción: ' . $e->getMessage()
            ], 500);
        }
    }
}
