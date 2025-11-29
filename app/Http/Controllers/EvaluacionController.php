<?php

namespace App\Http\Controllers;

use App\Services\EvaluacionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class EvaluacionController extends Controller
{
    protected $evaluacionService;

    public function __construct(EvaluacionService $evaluacionService)
    {
        $this->evaluacionService = $evaluacionService;
    }

    /**
     * Almacena una nueva evaluación para un competidor en una competencia específica.
     *
     * @param Request $request
     * @param int $id_competencia
     * @return JsonResponse
     */
    public function store(Request $request, int $id_competencia): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_competidor' => 'required|exists:competidor,id_competidor',
            'id_evaluadorAN' => 'required|exists:evaluador_an,id_evaluadorAN',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $datosEvaluacion = $request->only(['id_competidor', 'id_evaluadorAN']);
            $datosEvaluacion['fecha_evaluacion'] = now(); // Cambiar a now() sin toDateString()

            $evaluacion = $this->evaluacionService->crearEvaluacion($datosEvaluacion, $id_competencia);
            $evaluacion->load('competidor.persona', 'competencia', 'evaluadorAn.usuario', 'parametro');

            return response()->json($evaluacion->toArray(), 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al registrar la calificación.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualiza una evaluación existente.
     *
     * @param Request $request
     * @param int $id_evaluacion
     * @return JsonResponse
     */
    public function update(Request $request, int $id_evaluacion): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nota' => 'sometimes|required|numeric|min:0|max:100',
            'observaciones' => 'nullable|string',
            'estado' => 'sometimes|required|string|in:Pendiente,En Proceso,Calificado,Descalificado',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $datosEvaluacion = $request->only(['nota', 'observaciones', 'estado']);
            $datosEvaluacion['fecha_evaluacion'] = now(); // Cambiar a now() sin toDateString()

            $evaluacion = $this->evaluacionService->actualizarEvaluacion($id_evaluacion, $datosEvaluacion);
            $evaluacion->load('competidor.persona', 'competencia', 'evaluadorAn.usuario', 'parametro');

            return response()->json($evaluacion->toArray());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al actualizar la calificación.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Finaliza una evaluación, guardando la nota y marcándola como 'Calificado'.
     *
     * @param Request $request
     * @param int $id_evaluacion
     * @return JsonResponse
     */
    public function finalizarCalificacion(Request $request, int $id_evaluacion): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nota' => 'required|numeric|min:0|max:100',
            'observaciones' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $datosFinales = $request->only(['nota', 'observaciones']);
            $evaluacion = $this->evaluacionService->finalizarCalificacion($id_evaluacion, $datosFinales);
            $evaluacion->load('competidor.persona', 'competencia', 'evaluadorAn.usuario', 'parametro');

            return response()->json($evaluacion->toArray());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al finalizar la calificación.', 'error' => $e->getMessage()], 500);
        }
    }

    // Los otros métodos permanecen igual...
    /**
     * Obtiene todas las evaluaciones calificadas para una competencia.
     *
     * @param int $id_competencia
     * @return JsonResponse
     */
    public function getCalificados(int $id_competencia): JsonResponse
    {
        try {
            $evaluaciones = $this->evaluacionService->getCalificadosPorCompetencia($id_competencia);
            return response()->json($evaluaciones);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las evaluaciones calificadas.', 
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene la última evaluación de un competidor específico.
     *
     * @param int $id_competidor
     * @return JsonResponse
     */
    public function getEvaluacionPorCompetidor(int $id_competidor): JsonResponse
    {
        try {
            $evaluacion = $this->evaluacionService->getEvaluacionPorCompetidor($id_competidor);

            if (!$evaluacion) {
                return response()->json(['message' => 'No se encontró una calificación para este competidor.'], 404);
            }

            return response()->json($evaluacion->toArray());
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener la calificación del competidor.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}