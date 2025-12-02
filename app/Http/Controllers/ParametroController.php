<?php

namespace App\Http\Controllers;

// Usar la clase base correcta
use Illuminate\Routing\Controller;
use App\Http\Requests\StoreParametroRequest;
use App\Services\ParametroService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParametroController extends Controller
{
    public function __construct(
        protected ParametroService $service
    ) {}

    public function index(): JsonResponse
    {
        try {
            $result = $this->service->getAllParametros();
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(StoreParametroRequest $request): JsonResponse
    {
        try {
            // El Request valida y limpiamos los datos
            $this->service->guardarParametrosMasivos($request->validated()['area_niveles']);

            return response()->json([
                'success' => true,
                'message' => 'Parámetros guardados exitosamente.'
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getByOlimpiada(int $idOlimpiada): JsonResponse
    {
        try {
            $result = $this->service->getParametrosPorOlimpiada($idOlimpiada);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getParametrosByAreaNiveles(Request $request): JsonResponse
    {
        $request->validate(['ids' => 'required|string']);

        try {
            $ids = array_map('intval', explode(',', $request->input('ids')));
            $result = $this->service->getParametrosByAreaNiveles($ids);

            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getAllParametrosByGestiones(): JsonResponse
    {
        try {
            $result = $this->service->getAllParametrosByGestiones();
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Compatibilidad Legacy
    public function getParametrosGestionActual() {
        return response()->json(['message' => 'Use getByOlimpiada con el ID de gestión actual'], 501);
    }
}
