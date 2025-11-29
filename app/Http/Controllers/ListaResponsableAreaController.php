<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use App\Services\ListaResponsableAreaService;
use InvalidArgumentException;

class ListaResponsableAreaController extends Controller
{
    protected ListaResponsableAreaService $listaResponsableAreaService;

    public function __construct(ListaResponsableAreaService $listaResponsableAreaService)
    {
        $this->listaResponsableAreaService = $listaResponsableAreaService;
    }

    public function getNivelesPorArea(Request $request, $idArea): JsonResponse
    {
        $idArea = (int) $idArea;
        $niveles = $this->listaResponsableAreaService->getNivelesPorArea($idArea);

        return response()->json([
            'success' => true,
            'data' => ['niveles' => $niveles]
        ], 200);
    }

    public function getAreaPorResponsable(Request $request, $idResponsable): JsonResponse
    {
        $idResponsable = (int) $idResponsable;
        $areas = $this->listaResponsableAreaService->getAreaPorResponsable($idResponsable);

        return response()->json([
            'success' => true,
            'data' => ['areas' => $areas]
        ], 200);
    }

 public function listarPorAreaYNivel(
    Request $request,
    $idResponsable,
    $idArea,
    $idNivel,
    $grado,
    $genero = null,
    $departamento = null
): JsonResponse {
    try {
        $competidores = $this->listaResponsableAreaService->listarPorAreaYNivel(
            (int)$idResponsable,
            (int)$idArea,
            (int)$idNivel,
            (int)$grado,
            $genero,
            $departamento
        );

        return response()->json([
            'success' => true,
            'data' => ['competidores' => $competidores]
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al listar competidores: ' . $e->getMessage()
        ], 500);
    }
}
    public function getListaGrados(Request $request, int $idNivel): JsonResponse
    {
        try {
            $grados = $this->listaResponsableAreaService->getListaGrados((int)$idNivel);

            return response()->json([
                'success' => true,
                'data' => ['grados' => $grados]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los grados: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDepartamento(): JsonResponse
{
    try {
        $departamentos = $this->listaResponsableAreaService->getListaDepartamento();

        return response()->json([
            'success' => true,
            'data' => ['departamentos' => $departamentos]
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al obtener los departamentos: ' . $e->getMessage()
        ], 500);
    }
}
    public function getGenero(): JsonResponse
{
    try {
        $generos = $this->listaResponsableAreaService->getListaGeneros();

        return response()->json([
            'success' => true,
            'data' => ['generos' => $generos]
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al obtener los géneros: ' . $e->getMessage()
        ], 500);
    }
}

    public function getCompetidoresPorAreaYNivel(Request $request, int $idArea, int $idNivel): JsonResponse
    {
        try {
            $competidores = $this->listaResponsableAreaService->getCompetidoresPorAreaYNivel($idArea, $idNivel);

            if ($competidores->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => ['competidores' => []],
                    'message' => 'No se encontraron competidores para el área y nivel especificados.'
                ], 200);
            }

            return response()->json([
                'success' => true,
                'data' => ['competidores' => $competidores]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al listar competidores: ' . $e->getMessage()
            ], 500);
        }
    }
}
