<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\Reporte\GetHistorialRequest;
use App\Services\ReporteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function __construct(
        protected ReporteService $service
    ) {}

    /**
     * Endpoint Principal: Historial de Cambios de Calificaciones
     */
    public function historialCalificaciones(GetHistorialRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $resultado = $this->service->obtenerHistorial(
                limit: $validated['limit'],
                idArea: $validated['id_area'] ?? null,
                idsNivelesStr: $validated['ids_niveles'] ?? null,
                search: $validated['search'] ?? null
            );

            return response()->json($resultado);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Endpoint Auxiliar: Listar Áreas para el filtro
     */
    public function getAreas(): JsonResponse
    {
        try {
            $resultado = $this->service->listarAreasParaFiltro();
            return response()->json($resultado);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint Auxiliar: Listar Niveles de un Área para el filtro
     */
    public function getNivelesPorArea(int $idArea): JsonResponse
    {
        try {
            $resultado = $this->service->listarNivelesDeArea($idArea);
            return response()->json($resultado);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
