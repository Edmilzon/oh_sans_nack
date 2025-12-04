<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;

use App\Http\Requests\Evaluador\StoreEvaluadorRequest;
use App\Services\EvaluadorService;
use App\Model\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class EvaluadorController extends Controller
{
    public function __construct(
        protected EvaluadorService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {

            $query = Usuario::query();


            $query->join('persona', 'usuario.id_persona', '=', 'persona.id_persona')
                  ->select(
                      'usuario.*',
                      'persona.nombre',
                      'persona.apellido',
                      'persona.ci',
                      'persona.telefono'
                  );

            $query->whereHas('roles', function($q) {
                $q->where('nombre', 'Evaluador');
            });

            if ($search = $request->input('search')) {
                $query->where(function($q) use ($search) {
                    $q->where('persona.nombre', 'like', "%{$search}%")
                      ->orWhere('persona.apellido', 'like', "%{$search}%")
                      ->orWhere('persona.ci', 'like', "%{$search}%")
                      ->orWhere('usuario.email', 'like', "%{$search}%");
                });
            }

            if ($olimpiadaId = $request->input('olimpiada_id')) {
                $query->whereHas('roles', function($q) use ($olimpiadaId) {
                    $q->where('nombre', 'Evaluador')
                      ->where('usuario_rol.id_olimpiada', $olimpiadaId);
                });
            }

            if ($request->has('activo')) {
                 $activo = filter_var($request->input('activo'), FILTER_VALIDATE_BOOLEAN);
                 $query->where('usuario.estado', $activo);
            }

            $sortField = $request->input('sort_by', 'created_at');
            $sortDirection = $request->input('sort_order', 'desc');

            $allowedSorts = ['nombre', 'apellido', 'ci', 'email', 'created_at'];

            if (in_array($sortField, $allowedSorts)) {
                if (in_array($sortField, ['nombre', 'apellido', 'ci'])) {
                    $query->orderBy("persona.$sortField", $sortDirection);
                } else {
                    $query->orderBy("usuario.$sortField", $sortDirection);
                }
            }

            $perPage = $request->input('per_page', 15);
            $evaluadores = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Listado de evaluadores obtenido exitosamente.',
                'data'    => $evaluadores
            ]);

        } catch (\Exception $e) {
            Log::error('Error en buscador de evaluadores: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la lista de evaluadores.',
                'error'   => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(StoreEvaluadorRequest $request): JsonResponse
    {
        try {

            $data = $request->validated();

            $result = $this->service->createEvaluador($data);

            return response()->json([
                'success' => true,
                'message' => 'Evaluador registrado exitosamente.',
                'data'    => $result
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            Log::error('Error creando evaluador: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el evaluador.',
                'error'   => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $evaluador = $this->service->getEvaluadorById($id);

            if (!$evaluador) {
                return response()->json([
                    'success' => false,
                    'message' => 'Evaluador no encontrado.'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'success' => true,
                'message' => 'Evaluador obtenido exitosamente.',
                'data'    => $evaluador
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el evaluador.',
                'error'   => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function addAsignaciones(Request $request, string $ci): JsonResponse
    {
        $request->validate([
            'id_olimpiada'   => 'required|integer|exists:olimpiada,id_olimpiada',
            'area_nivel_ids' => 'required|array|min:1',
            'area_nivel_ids.*' => 'integer|exists:area_nivel,id_area_nivel'
        ]);

        try {
            $result = $this->service->addAsignacionesToEvaluador(
                $ci,
                $request->id_olimpiada,
                $request->area_nivel_ids
            );

            return response()->json([
                'success' => true,
                'message' => 'Nuevas asignaciones agregadas correctamente.',
                'data'    => $result
            ]);

        } catch (\Exception $e) {
            Log::error("Error agregando asignaciones a CI $ci: " . $e->getMessage());

            $status = str_contains($e->getMessage(), 'no existe') ? 404 : 500;

            return response()->json([
                'success' => false,
                'message' => 'No se pudieron agregar las asignaciones.',
                'error'   => $e->getMessage()
            ], $status);
        }
    }

    public function getAreasNivelesById($id): JsonResponse
    {
        try {
            $evaluador = $this->service->getEvaluadorById($id);

            if (!$evaluador) {
                return response()->json([
                    'success' => false,
                    'message' => 'Evaluador no encontrado.'
                ], Response::HTTP_NOT_FOUND);
            }

            $areasAsignadas = $evaluador['areas_asignadas'] ?? [];

            return response()->json([
                'success' => true,
                'message' => 'Áreas y niveles del evaluador obtenidos exitosamente.',
                'data'    => $areasAsignadas
            ]);

        } catch (\Exception $e) {
            Log::error("Error obteniendo áreas y niveles para evaluador ID $id: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las áreas y niveles del evaluador.',
                'error'   => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
