<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\StoreParametroRequest;
use App\Services\ParametroService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Model\Olimpiada;

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
            $parametrosGuardados = $this->service->guardarParametrosMasivos($request->validated()['area_niveles']);
            
            $data = collect($parametrosGuardados)->map(function($parametro) {
                return [
                    'id_parametro' => $parametro->id_parametro,
                    'id_area_nivel' => $parametro->id_area_nivel,
                    'nota_min_aprobacion' => $parametro->nota_min_aprobacion,
                    'cantidad_maxima' => $parametro->cantidad_maxima,
                    'area_nivel' => [
                        'area' => $parametro->areaNivel->areaOlimpiada->area->nombre ?? null,
                        'nivel' => $parametro->areaNivel->nivel->nombre ?? null
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Parámetros guardados exitosamente.',
                'data' => $data
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
        $request->validate(['ids' => 'nullable|string']);

        try {
            $idsInput = $request->input('ids', '');
            
            if (empty($idsInput)) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'No se proporcionaron IDs de área-nivel'
                ]);
            }
            
            $ids = array_map('intval', explode(',', $idsInput));
            $ids = array_filter($ids, function($id) {
                return is_numeric($id) && $id > 0;
            });
            
            if (empty($ids)) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Los IDs proporcionados no son válidos'
                ]);
            }
            
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

    public function getParametrosGestionActual(): JsonResponse
    {
        try {
            $olimpiadaActual = Olimpiada::where('estado', true)->first();
            
            if (!$olimpiadaActual) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay olimpiada activa en este momento.'
                ], 404);
            }
            
            $result = $this->service->getParametrosPorOlimpiada($olimpiadaActual->id_olimpiada);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}