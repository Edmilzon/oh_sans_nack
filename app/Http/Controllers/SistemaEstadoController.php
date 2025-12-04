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

    public function index(Request $request): JsonResponse
    {
        $estado = $this->service->obtenerEstadoDelSistema();

        return response()->json([
            'success' => true,
            'data' => $estado
        ]);
    }
}
