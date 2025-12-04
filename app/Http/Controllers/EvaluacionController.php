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

    public function store(Request $request, int $id_examen_conf): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_competidor' => 'required|exists:competidor,id_competidor',
            'id_evaluador_an' => 'required|exists:evaluador_an,id_evaluador_an',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {

            $data = [
                'id_competidor' => $request->input('id_competidor'),
                'id_evaluador_an' => $request->input('id_evaluador_an') ?: $request->input('id_evaluadorAN'),
            ];

            $evaluacion = $this->evaluacionService->crearEvaluacion($data, $id_examen_conf);
            $evaluacion->load('competidor.persona', 'examen.competencia', 'evaluadorAn.usuario.persona');

            return response()->json($evaluacion->toArray(), 201);
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Este competidor ya está siendo evaluado por otra persona.') {
                return response()->json(['message' => $e->getMessage()], 409);
            }
            return response()->json(['message' => 'Error al registrar la calificación.', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, int $id_evaluacion): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nota' => 'sometimes|required|numeric|min:0|max:100',
            'observacion' => 'nullable|string',
            'estado_competidor' => 'sometimes|required|string|in:PENDIENTE,EN PROCESO,CALIFICADO,DESCALIFICADO',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $datosEvaluacion = $request->only(['nota', 'observacion', 'estado_competidor']);
            
            $evaluacion = $this->evaluacionService->actualizarEvaluacion($id_evaluacion, $datosEvaluacion);
            $evaluacion->load('competidor.persona', 'examen.competencia', 'evaluadorAn.usuario.persona');

            return response()->json($evaluacion->toArray());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al actualizar la calificación.', 'error' => $e->getMessage()], 500);
        }
    }

    public function finalizarCalificacion(Request $request, int $id_evaluacion): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nota' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $datosFinales = $request->only(['nota', 'observaciones']);
            $evaluacion = $this->evaluacionService->finalizarCalificacion($id_evaluacion, $datosFinales);
            $evaluacion->load('competidor.persona', 'examen.competencia', 'evaluadorAn.usuario.persona');

            return response()->json($evaluacion->toArray());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al finalizar la calificación.', 'error' => $e->getMessage()], 500);
        }
    }

    public function descalificar(Request $request, int $id_evaluacion): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'observaciones' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $datosDescalificacion = $request->only(['observaciones']);
            $evaluacion = $this->evaluacionService->descalificarCompetidor($id_evaluacion, $datosDescalificacion);
            $evaluacion->load('competidor.persona', 'examen.competencia', 'evaluadorAn.usuario.persona');

            return response()->json($evaluacion->toArray());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al descalificar al competidor.', 'error' => $e->getMessage()], 500);
        }
    }

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