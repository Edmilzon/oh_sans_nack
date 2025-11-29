<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\StoreParametroRequest;
use App\Services\ParametroService;
use App\Services\OlimpiadaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParametroController extends Controller
{
    protected $parametroService;
    protected $olimpiadaService;

    public function __construct(ParametroService $parametroService, OlimpiadaService $olimpiadaService)
    {
        $this->parametroService = $parametroService;
        $this->olimpiadaService = $olimpiadaService;
    }

    public function index(): JsonResponse
    {
        try {
            $result = $this->parametroService->getAllParametros();

            return response()->json([
                'success' => true,
                'data' => $result['parametros'],
                'total' => $result['total'],
                'message' => $result['message']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los parámetros: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getByOlimpiada(int $idOlimpiada): JsonResponse
    {
        try {
            $result = $this->parametroService->getParametrosByOlimpiada($idOlimpiada);

            return response()->json([
                'success' => true,
                'data' => $result['parametros'],
                'total' => $result['total'],
                'message' => $result['message']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los parámetros: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(StoreParametroRequest $request): JsonResponse
    {
        try {
            $result = $this->parametroService->createOrUpdateParametros($request->validated());

            $response = [
                'success' => true,
                'data' => $result['parametros_actualizados'],
                'total_procesados' => $result['total_procesados'],
                'message' => $result['message']
            ];

            if (isset($result['errors'])) {
                $response['errors'] = $result['errors'];
            }

            return response()->json($response, 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar los parámetros: ' . $e->getMessage()
            ], 500);
        }
    }

     public function getParametrosGestionActual(): JsonResponse
    {
        try {
            $olimpiadaActual = $this->olimpiadaService->obtenerOlimpiadaActual();
            
            $result = $this->parametroService->getParametrosByOlimpiada($olimpiadaActual->id_olimpiada);

            return response()->json([
                'success' => true,
                'data' => $result['parametros'],
                'total' => $result['total'],
                'olimpiada_actual' => [
                    'id_olimpiada' => $olimpiadaActual->id_olimpiada,
                    'gestion' => $olimpiadaActual->gestion,
                    'nombre' => $olimpiadaActual->nombre
                ],
                'message' => $result['message']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los parámetros: ' . $e->getMessage()
            ], 500);
        }
    }


    public function getAllParametrosByGestiones(): JsonResponse
    {
    try {
        $result = $this->parametroService->getAllParametrosByGestiones();

        return response()->json([
            'success' => true,
            'data' => $result['gestiones'],
            'total_gestiones' => $result['total_gestiones'],
            'message' => $result['message']
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al obtener los parámetros por gestiones: ' . $e->getMessage()
        ], 500);
    }
    }

    public function getParametrosByAreaNiveles(Request $request): JsonResponse
{
    try {
        $request->validate([
            'ids' => 'required|string'
        ]);

        $idsAreaNivel = array_map('intval', explode(',', $request->ids));

        foreach ($idsAreaNivel as $id) {
            if ($id <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Los IDs deben ser números positivos'
                ], 400);
            }
        }

        $result = $this->parametroService->getParametrosByAreaNiveles($idsAreaNivel);

        return response()->json([
            'success' => true,
            'data' => $result,
            'message' => $result['message']
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al obtener los parámetros históricos: ' . $e->getMessage()
        ], 500);
    }
}
}