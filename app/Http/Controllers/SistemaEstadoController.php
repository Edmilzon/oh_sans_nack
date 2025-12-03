<?php

namespace App\Http\Controllers;

use App\Services\SistemaEstadoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SistemaEstadoController extends Controller
{
    public function __construct(
        protected SistemaEstadoService $service
    ) {}

    /**
     * Endpoint público para obtener la configuración actual del evento.
     */
    public function index(Request $request): JsonResponse
    {
        $estado = $this->service->obtenerEstadoDelSistema();

        // Si no hay gestión, retornamos 404 o 200 con estado inactivo (prefiero 200 para frontend)
        return response()->json([
            'success' => true,
            'data' => $estado
        ]);
    }
}
