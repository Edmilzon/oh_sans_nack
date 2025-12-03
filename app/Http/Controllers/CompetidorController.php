<?php

namespace App\Http\Controllers;

use App\Services\CompetidorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class CompetidorController extends Controller
{
    public function __construct(
        protected CompetidorService $competidorService
    ) {}

    /**
     * Descalifica a un competidor de la competencia.
     *
     * @param Request $request
     * @param int $id_competidor
     * @return JsonResponse
     */
    public function descalificar(Request $request, int $id_competidor): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'observaciones' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $competidor = $this->competidorService->descalificarCompetidor($id_competidor, $request->input('observaciones'));
            return response()->json(['message' => 'Competidor descalificado exitosamente.', 'data' => $competidor]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al descalificar al competidor.', 'error' => $e->getMessage()], 500);
        }
    }
}
