<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\Cronograma\StoreCronogramaRequest;
use App\Http\Requests\Cronograma\UpdateCronogramaRequest;
use App\Services\CronogramaFaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CronogramaFaseController extends Controller
{
    public function __construct(
        protected CronogramaFaseService $service
    ) {}

    public function index(): JsonResponse
    {
        return response()->json($this->service->listarTodos());
    }

    public function store(StoreCronogramaRequest $request): JsonResponse
    {
        $cronograma = $this->service->crear($request->validated());
        return response()->json($cronograma, 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->service->obtenerPorId($id));
    }

    public function update(UpdateCronogramaRequest $request, int $id): JsonResponse
    {
        $cronograma = $this->service->actualizar($id, $request->all());
        return response()->json($cronograma);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->eliminar($id);
        return response()->json(['message' => 'Cronograma eliminado correctamente']);
    }

    public function listarActuales(): JsonResponse
    {
        try {
            $cronogramas = $this->service->listarVigentes();

            if ($cronogramas->isEmpty()) {
                return response()->json([
                    'message' => 'No hay cronogramas activos para la gestión actual.'
                ], 404);
            }

            return response()->json($cronogramas);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
