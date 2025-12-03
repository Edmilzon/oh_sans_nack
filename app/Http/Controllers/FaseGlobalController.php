<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Services\FaseService;
use Illuminate\Http\JsonResponse;

class FaseGlobalController extends Controller
{
    public function __construct(
        protected FaseService $service
    ) {}

    public function listarActuales(): JsonResponse
    {
        $fases = $this->service->listarFasesDeOlimpiadaActual();

        if ($fases->isEmpty()) {
            return response()->json(['message' => 'No hay una olimpiada activa o fases configuradas.'], 404);
        }

        return response()->json($fases);
    }
}
