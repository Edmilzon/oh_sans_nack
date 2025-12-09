<?php

namespace App\Http\Controllers;

use App\Services\ExamenService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Model\Competencia;

class ExamenController extends Controller
{
    protected $examenService;

    public function __construct(ExamenService $examenService)
    {
        $this->examenService = $examenService;
    }

    public function store(Request $request, int $id_competencia): JsonResponse
    {
        Competencia::findOrFail($id_competencia);

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'ponderacion' => 'required|numeric|min:0|max:100',
            'maxima_nota' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $examen = $this->examenService->crearExamen($request->all(), $id_competencia);
            return response()->json($examen, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear el examen.', 'error' => $e->getMessage()], 400);
        }
    }

    public function index(int $id_competencia): JsonResponse
    {
        $examenes = $this->examenService->obtenerExamenesPorCompetencia($id_competencia);
        return response()->json($examenes);
    }

    public function show(int $id_examen_conf): JsonResponse
    {
        $examen = $this->examenService->obtenerExamenPorId($id_examen_conf);

        if (!$examen) {
            return response()->json(['message' => 'Examen no encontrado.'], 404);
        }

        return response()->json($examen);
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

        $examenes = $this->examenService->obtenerExamenesPorAreaYNivel($request->id_area, $request->id_nivel);

        return response()->json($examenes);
    }
}