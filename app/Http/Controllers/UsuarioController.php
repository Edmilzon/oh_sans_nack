<?php

namespace App\Http\Controllers;

use App\Services\UsuarioService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class UsuarioController extends Controller
{
    protected $usuarioService;

    public function __construct(UsuarioService $usuarioService)
    {
        $this->usuarioService = $usuarioService;
    }

    /**
     * Maneja la solicitud de login del usuario.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $result = $this->usuarioService->login($request->only('email', 'password'));

        if (!$result) {
            return response()->json(['message' => 'Credenciales no autorizadas'], 401);
        }

        return response()->json($result);
    }

    /**
     * Muestra la información detallada de un usuario por su CI.
     *
     * @param string $ci
     * @return JsonResponse
     */
    public function showByCi(string $ci): JsonResponse
    {
        $usuario = $this->usuarioService->getUsuarioDetalladoPorCi($ci);

        if (!$usuario) {
            return response()->json([
                'message' => 'Usuario no encontrado con el CI proporcionado.'
            ], 404);
        }

        return response()->json([
            'message' => 'Usuario obtenido exitosamente',
            'data' => $usuario
        ]);
    }
}