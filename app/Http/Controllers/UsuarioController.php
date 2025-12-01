<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\UsuarioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function __construct(
        protected UsuarioService $usuarioService
    ) {}

    /**
     * Login de usuario.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        // La validación ya se hizo en LoginRequest
        $result = $this->usuarioService->login($request->validated());

        if (!$result) {
            return response()->json(['message' => 'Credenciales no autorizadas'], 401);
        }

        return response()->json($result);
    }

    /**
     * Obtener usuario detallado por CI.
     */
    public function showByCi(string $ci): JsonResponse
    {
        try {
            $usuario = $this->usuarioService->getUsuarioDetalladoPorCi($ci);

            if (!$usuario) {
                return response()->json([
                    'message' => 'Usuario no encontrado con el CI proporcionado.'
                ], 404);
            }

            return response()->json([
                'message' => 'Usuario obtenido exitosamente',
                'data'    => $usuario
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error interno del servidor',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
