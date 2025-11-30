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
            'id_evaluadorAN' => 'required|exists:evaluador_an,id_evaluador_an',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $evaluacion = $this->evaluacionService->crearEvaluacion($request->all(), $id_competencia);
            $evaluacion->load('inscripcion.competidor.persona', 'competencia', 'evaluadorAn.usuario');

            return response()->json($evaluacion->toArray(), 201);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Este competidor ya está siendo evaluado por otra persona.') {
                return response()->json(['message' => $e->getMessage()], 409);
            }
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
            'nota_evalu' => 'sometimes|required|numeric|min:0|max:100',
            'observacion_evalu' => 'nullable|string',
            'estado_competidor_eva' => 'sometimes|required|string|in:PENDIENTE,EN PROCESO,CALIFICADO,DESCALIFICADO',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $datosEvaluacion = $request->only(['nota_evalu', 'observacion_evalu', 'estado_competidor_eva']);
            $datosEvaluacion['fecha_evalu'] = now();

            $evaluacion = $this->evaluacionService->actualizarEvaluacion($id_evaluacion, $datosEvaluacion);
            $evaluacion->load('inscripcion.competidor.persona', 'competencia', 'evaluadorAn.usuario');

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
            'nota' => 'required|numeric|min:0', // La nota máxima se valida en otro lado si es necesario
            'observaciones' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $datosFinales = $request->only(['nota', 'observaciones']);
            $evaluacion = $this->evaluacionService->finalizarCalificacion($id_evaluacion, $datosFinales);
            $evaluacion->load('inscripcion.competidor.persona', 'competencia', 'evaluadorAn.usuario');

            return response()->json($evaluacion->toArray());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al finalizar la calificación.', 'error' => $e->getMessage()], 500);
        }
    }

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
    public function getUltimaPorCompetidor(int $id_competidor): JsonResponse
    {
        try {
            $evaluacion = $this->evaluacionService->getUltimaPorCompetidor($id_competidor);

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