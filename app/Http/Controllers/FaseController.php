<?php

namespace App\Http\Controllers;

use App\Services\FaseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class FaseController extends Controller
{
    protected $faseService;

    public function __construct(FaseService $faseService)
    {
        $this->faseService = $faseService;
    }

    public function indexGlobales(): JsonResponse
    {
        $fases = $this->faseService->obtenerFasesGlobales();
        return response()->json($fases);
    }

    public function index(int $id_area_nivel): JsonResponse
    {
        $fases = $this->faseService->obtenerFasesPorAreaNivel($id_area_nivel);
        return response()->json($fases);
    }

    public function store(Request $request, int $id_area_nivel): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'orden' => 'sometimes|integer|min:1',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $datos = $request->all();
        $datos['id_area_nivel'] = $id_area_nivel;
        $datos['estado'] = 'Pendiente';

        $fase = $this->faseService->crearFaseConCompetencia($datos);

        return response()->json($fase, 201);
    }

    public function show(int $id_fase): JsonResponse
    {
        $fase = $this->faseService->obtenerFasePorId($id_fase);
        if (!$fase) {
            return response()->json(['message' => 'Fase no encontrada'], 404);
        }
        return response()->json($fase);
    }

    public function update(Request $request, int $id_fase): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|required|string|max:255',
            'orden' => 'sometimes|required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $actualizado = $this->faseService->actualizarFase($id_fase, $request->all());

        if (!$actualizado) {
            return response()->json(['message' => 'Fase no encontrada'], 404);
        }

        $fase = $this->faseService->obtenerFasePorId($id_fase);
        return response()->json($fase);
    }

    public function destroy(int $id_fase): JsonResponse
    {
        $eliminado = $this->faseService->eliminarFase($id_fase);

        if (!$eliminado) {
            return response()->json(['message' => 'Fase no encontrada'], 404);
        }

        return response()->json(null, 204);
    }

    public function listarAccionesSistema(): JsonResponse
    {
        $acciones = $this->faseService->listarAccionesSistema();
        return response()->json($acciones);
    }

    public function getConfiguracionAccionesPorGestion(int $idGestion): JsonResponse
    {
        $configuracion = $this->faseService->getConfiguracionAccionesPorGestion($idGestion);
        return response()->json($configuracion);
    }

    public function guardarConfiguracionAccionesPorGestion(Request $request, int $idGestion): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'accionesPorFase' => 'required|array',
            'accionesPorFase.*.idAccion' => 'required|integer',
            'accionesPorFase.*.idFase' => 'required|integer',
            'accionesPorFase.*.habilitada' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $this->faseService->guardarConfiguracionAccionesPorGestion($idGestion, $request->input('accionesPorFase'));

        return response()->json(['message' => 'Configuración guardada exitosamente.']);
    }

    public function actualizarAccionEnFase(Request $request, int $idGestion, int $idFase, int $idAccion): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'habilitada' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $this->faseService->actualizarAccionHabilitada($idGestion, $idFase, $idAccion, $request->input('habilitada'));

        return response()->json(['message' => 'El estado de la acción ha sido actualizado.']);
    }

    public function getAccionesHabilitadas(int $idGestion, int $idFase): JsonResponse
    {
        $acciones = $this->faseService->getAccionesHabilitadas($idGestion, $idFase);
        return response()->json($acciones);
    }

    public function getFaseDetails(int $id_fase): JsonResponse
    {
        $detalles = $this->faseService->getFaseDetails($id_fase);

        if (is_null($detalles)) {
            return response()->json(['message' => 'Fase no encontrada o no pertenece a la gestión 2025.'], 404);
        }

        return response()->json($detalles);
    }

    public function getSubFases(int $id_area, int $id_nivel, int $id_olimpiada): JsonResponse
    {
        $subFases = $this->faseService->getSubFasesDetails($id_area, $id_nivel, $id_olimpiada);

        return response()->json([
            'success' => true,
            'message' => 'Sub-fases obtenidas correctamente',
            'data' => $subFases,
        ]);
    }
}
