<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use App\Model\ParametroMedallero;
use App\Services\MedalleroService;

class MedalleroController extends Controller
{
    protected MedalleroService $medalleroService;

    public function __construct(MedalleroService $medalleroService)
    {
        $this->medalleroService = $medalleroService;
    }

    public function getAreaPorResponsable(Request $request, $idResponsable): JsonResponse
    {
        $idResponsable = (int) $idResponsable;
        $areas = $this->medalleroService->getAreaPorResponsable($idResponsable);

        return response()->json([
            'success' => true,
            'data' => ['areas' => $areas]
        ], 200);
    }

    public function getNivelesPorArea(Request $request, $idArea): JsonResponse
    {
        $idArea = (int) $idArea;
        $niveles = $this->medalleroService->getNivelesPorArea($idArea);

        return response()->json([
            'success' => true,
            'data' => ['niveles' => $niveles]
        ], 200);
    }
    public function guardarMedallero(Request $request): JsonResponse
{
    $data = $request->validate([
        'niveles' => 'required|array',
        'niveles.*.id_area_nivel' => 'required|integer',
        'niveles.*.oro' => 'required|integer',
        'niveles.*.plata' => 'required|integer',
        'niveles.*.bronce' => 'required|integer',
        'niveles.*.menciones' => 'required|integer',
    ]);

    $resultados = $this->medalleroService->guardarMedallero($data['niveles']);

    return response()->json([
        'success' => true,
        'message' => 'Operación realizada correctamente',
        'resultados' => $resultados
    ]);
}


}
