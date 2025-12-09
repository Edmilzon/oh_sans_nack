<?php

namespace App\Http\Controllers;

use App\Services\CompetenciaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class CompetenciaController extends Controller
{
    protected $competenciaService;

    public function __construct(CompetenciaService $competenciaService)
    {
        $this->competenciaService = $competenciaService;
    }

    /**
     * Crea una nueva competencia.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_area_nivel' => 'required|exists:area_nivel,id_area_nivel',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $competencia = $this->competenciaService->crearCompetencia($request->all());

        return response()->json($competencia, 201);
    }

    /**
     * Lista todas las competencias.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $competencias = $this->competenciaService->obtenerCompetencias();
        return response()->json($competencias);
    }

    /**
     * Muestra una competencia específica.
     *
     * @param int $id_competencia
     * @return JsonResponse
     */
    public function show(int $id_competencia): JsonResponse
    {
        $competencia = $this->competenciaService->obtenerCompetenciaPorId($id_competencia);

        if (!$competencia) {
            return response()->json(['message' => 'Competencia no encontrada.'], 404);
        }

        return response()->json($competencia);
    }

    public function getByAreaAndNivel(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_area' => 'required|integer|exists:areas,id_area',
            'id_nivel' => 'required|integer|exists:niveles,id_nivel',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $competencia = $this->competenciaService->obtenerCompetenciaPorAreaYNivel($request->id_area, $request->id_nivel);

        if (!$competencia) {
            return response()->json(['message' => 'Competencia no encontrada.'], 404);
        }

        return response()->json($competencia);
    }

    public function getByAreaNivelId(int $id_area_nivel): JsonResponse
    {
        $competencias = $this->competenciaService->obtenerCompetenciasPorAreaNivelId($id_area_nivel);
        return response()->json($competencias);
    }
}