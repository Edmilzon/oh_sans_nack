<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Services\FaseService;
use App\Http\Requests\Fase\StoreFaseRequest;
use App\Http\Requests\Fase\UpdateConfiguracionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FaseController extends Controller
{
    public function __construct(
        protected FaseService $service
    ) {}

    // ... (Métodos indexGlobales, listarAcciones, getConfiguracion, guardarConfiguracion, actualizarAccionEnFase, index, store, update, destroy se mantienen igual) ...
    public function indexGlobales(): JsonResponse{
        return response()->json($this->service->obtenerFasesGlobales());
    }
    public function listarAccionesSistema(): JsonResponse {
        return response()->json($this->service->listarAccionesSistema());
    }
    public function getConfiguracionAccionesPorGestion(int $id): JsonResponse {
        return response()->json($this->service->getConfiguracionAccionesPorGestion($id));
    }

    public function guardarConfiguracionAccionesPorGestion(UpdateConfiguracionRequest $r, int $id): JsonResponse {
        $this->service->guardarConfiguracionAcciones($id, $r->validated()['accionesPorFase']);
        return response()->json(['message' => 'Configuración guardada.']);
    }
    public function actualizarAccionEnFase(Request $r, int $idG, int $idF, int $idA): JsonResponse {
        $r->validate(['habilitada' => 'required|boolean']);
        $this->service->actualizarAccionHabilitada($idG, $idF, $idA, $r->input('habilitada'));
        return response()->json(['message' => 'Estado actualizado.']);
    }
    public function index(int $id): JsonResponse { return response()->json($this->service->obtenerFasesPorAreaNivel($id)); }
    public function store(StoreFaseRequest $r, int $id): JsonResponse {
        try {
            return response()->json($this->service->crearFase($r->validated(), $id), 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function update(Request $r, int $id): JsonResponse {
        $fase = $this->service->actualizarFase($id, $r->all());
        return $fase ? response()->json($fase) : response()->json(['message' => 'No encontrado'], 404);
    }
    public function destroy(int $id): JsonResponse {
        return $this->service->eliminarFase($id) ? response()->json(['message' => 'Eliminado']) : response()->json(['message' => 'No encontrado'], 404);
    }

    // --- NUEVO ENDPOINT: CAMBIAR ESTADO ---
    public function updateEstado(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'estado' => 'required|string|in:NO_INICIADA,EN_EVALUACION,FINALIZADA'
        ]);

        try {
            $data = $this->service->cambiarEstadoFase($id, $request->input('estado'));
            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado.',
                'data'    => $data
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // --- REPORTE ---
    public function getSubFases(int $id_area, int $id_nivel, int $id_olimpiada): JsonResponse
    {
        $data = $this->service->getSubFasesDetails($id_area, $id_nivel, $id_olimpiada);
        return response()->json([
            'success' => true,
            'data'    => $data
        ]);
    }
}
