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

    /**
     * Crea un nuevo examen para una competencia.
     *
     * @param Request $request
     * @param int $id_competencia
     * @return JsonResponse
     */
    public function store(Request $request, int $id_competencia): JsonResponse
    {
        // Asegurarse que la competencia exista primero
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

    /**
     * Lista todos los exámenes de una competencia.
     *
     * @param int $id_competencia
     * @return JsonResponse
     */
    public function index(int $id_competencia): JsonResponse
    {
        $examenes = $this->examenService->obtenerExamenesPorCompetencia($id_competencia);
        return response()->json($examenes);
    }

    /**
     * Muestra un examen específico.
     *
     * @param int $id_examen_conf
     * @return JsonResponse
     */
    public function show(int $id_examen_conf): JsonResponse
    {
        $examen = $this->examenService->obtenerExamenPorId($id_examen_conf);

        if (!$examen) {
            return response()->json(['message' => 'Examen no encontrado.'], 404);
        }

        return response()->json($examen);
    }
}