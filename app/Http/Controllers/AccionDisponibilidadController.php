<?php

namespace App\Http\Controllers;

use App\Services\AccionDisponibilidadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class AccionDisponibilidadController extends Controller
{
    public function __construct(
        protected AccionDisponibilidadService $service
    ) {}

    public function index(int $idRol, int $idFaseGlobal, int $idGestion): JsonResponse
    {
        $acciones = $this->service->listarAcciones($idRol, $idFaseGlobal, $idGestion);

        return response()->json($acciones);
    }
}
