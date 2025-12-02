<?php

namespace App\Http\Controllers;

// Usamos la clase base del framework para evitar conflictos de Intelephense
use Illuminate\Routing\Controller;
use App\Services\FaseService;
use App\Http\Requests\Fase\StoreFaseRequest;
use App\Http\Requests\Fase\UpdateConfiguracionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FaseController extends Controller
{
    public function __construct(
        protected FaseService $service
    ) {}

    // --- CONFIGURACIÓN GLOBAL ---

    public function indexGlobales(): JsonResponse
    {
        return response()->json($this->service->obtenerFasesGlobales());
    }

    public function listarAccionesSistema(): JsonResponse
    {
        return response()->json($this->service->listarAccionesSistema());
    }

    public function getConfiguracionAccionesPorGestion(int $idGestion): JsonResponse
    {
        return response()->json($this->service->getConfiguracionAccionesPorGestion($idGestion));
    }

    public function guardarConfiguracionAccionesPorGestion(UpdateConfiguracionRequest $request, int $idGestion): JsonResponse
    {
        $this->service->guardarConfiguracionAccionesPorGestion(
            $idGestion,
            $request->validated()['accionesPorFase']
        );
        return response()->json(['message' => 'Configuración guardada exitosamente.']);
    }

    public function actualizarAccionEnFase(Request $request, int $idGestion, int $idFase, int $idAccion): JsonResponse
    {
        $request->validate(['habilitada' => 'required|boolean']);
        $this->service->actualizarAccionHabilitada($idGestion, $idFase, $idAccion, $request->input('habilitada'));
        return response()->json(['message' => 'El estado de la acción ha sido actualizado.']);
    }

    public function getAccionesHabilitadas(int $idGestion, int $idFase): JsonResponse
    {
        return response()->json($this->service->getAccionesHabilitadas($idGestion, $idFase));
    }

    // --- FASES ESPECÍFICAS (COMPETENCIAS) ---

    public function index(int $id_area_nivel): JsonResponse
    {
        return response()->json($this->service->obtenerFasesPorAreaNivel($id_area_nivel));
    }

    public function store(StoreFaseRequest $request, int $id_area_nivel): JsonResponse
    {
        try {
            $fase = $this->service->crearFase($request->validated(), $id_area_nivel);
            return response()->json($fase, 201);
        } catch (\Exception $e) {
            Log::error("Error creando fase: " . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        $fase = $this->service->obtenerFasePorId($id);
        return $fase ? response()->json($fase) : response()->json(['message' => 'Fase no encontrada'], 404);
    }

    public function getFaseDetails(int $id): JsonResponse
    {
        $detalles = $this->service->getFaseDetails($id);
        return $detalles ? response()->json($detalles) : response()->json(['message' => 'Fase no encontrada'], 404);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate(['nombre' => 'sometimes|string|max:255']);
        $fase = $this->service->actualizarFase($id, $request->all());
        return $fase ? response()->json($fase) : response()->json(['message' => 'Fase no encontrada'], 404);
    }

    public function destroy(int $id): JsonResponse
    {
        return $this->service->eliminarFase($id)
            ? response()->json(['message' => 'Fase eliminada'], 200)
            : response()->json(['message' => 'Fase no encontrada'], 404);
    }

    public function getSubFases(int $id_area, int $id_nivel, int $id_olimpiada): JsonResponse
    {
        try {
            $data = $this->service->getSubFasesDetails($id_area, $id_nivel, $id_olimpiada);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateEstado(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'estado' => 'required|string|in:NO_INICIADA,EN_EVALUACION,FINALIZADA'
        ]);

        try {
            $data = $this->service->cambiarEstadoFase($id, $request->input('estado'));
            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado correctamente.',
                'data'    => $data
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
