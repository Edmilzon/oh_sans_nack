<?php

namespace App\Http\Controllers;

use App\Services\UsuarioAccionesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UsuarioAccionesController extends Controller
{
    public function __construct(
        protected UsuarioAccionesService $service
    ) {}

    public function index(Request $request, int $idFaseGlobal, int $idGestion): JsonResponse
    {
        $idUsuario = $request->route('id_usuario');

        $acciones = $this->service->obtenerAccionesCombinadas($idUsuario, $idFaseGlobal, $idGestion);

        return response()->json([
            'success' => true,
            'data' => $acciones,
            'roles_detected' => 'Multi-Rol automático'
        ]);
    }
}
