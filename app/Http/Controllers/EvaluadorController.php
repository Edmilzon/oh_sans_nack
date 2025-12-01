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

    /**
     * GET /api/v1/evaluadores
     * Buscador Avanzado: Lista evaluadores con filtros, búsqueda y paginación.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // 1. Iniciar Query sobre el modelo Usuario
            $query = Usuario::query();

            // 2. Unir con Persona para búsquedas de texto (Nombre/Apellido/CI)
            $query->join('persona', 'usuario.id_persona', '=', 'persona.id_persona')
                  ->select(
                      'usuario.*',
                      'persona.nombre',
                      'persona.apellido',
                      'persona.ci',
                      'persona.telefono'
                  );

            // 3. FILTRO CRÍTICO: Solo usuarios que tengan (o hayan tenido) el rol 'Evaluador'
            $query->whereHas('roles', function($q) {
                $q->where('nombre', 'Evaluador');
            });

            // 4. BÚSQUEDA (Search Bar)
            if ($search = $request->input('search')) {
                $query->where(function($q) use ($search) {
                    $q->where('persona.nombre', 'like', "%{$search}%")
                      ->orWhere('persona.apellido', 'like', "%{$search}%")
                      ->orWhere('persona.ci', 'like', "%{$search}%")
                      ->orWhere('usuario.email', 'like', "%{$search}%");
                });
            }

            // 5. FILTROS ADICIONALES

            // Filtro por Gestión (Olimpiada específica)
            if ($olimpiadaId = $request->input('olimpiada_id')) {
                $query->whereHas('roles', function($q) use ($olimpiadaId) {
                    $q->where('nombre', 'Evaluador')
                      ->where('usuario_rol.id_olimpiada', $olimpiadaId);
                });
            }

            // Filtro por Estado (Activo/Inactivo)
            if ($request->has('activo')) {
                 $activo = filter_var($request->input('activo'), FILTER_VALIDATE_BOOLEAN);
                 $query->where('usuario.estado', $activo);
            }

            // 6. ORDENAMIENTO DINÁMICO
            $sortField = $request->input('sort_by', 'created_at'); // Default: fecha creación
            $sortDirection = $request->input('sort_order', 'desc');

            $allowedSorts = ['nombre', 'apellido', 'ci', 'email', 'created_at'];

            if (in_array($sortField, $allowedSorts)) {
                if (in_array($sortField, ['nombre', 'apellido', 'ci'])) {
                    $query->orderBy("persona.$sortField", $sortDirection);
                } else {
                    $query->orderBy("usuario.$sortField", $sortDirection);
                }
            }

            // 7. PAGINACIÓN
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

    /**
     * POST /api/v1/evaluadores
     * Crea un nuevo evaluador desde cero (Persona + Usuario + Rol + Áreas).
     * Usa StoreEvaluadorRequest para validaciones automáticas.
     */
    public function store(StoreEvaluadorRequest $request): JsonResponse
    {
        try {
            // Laravel ya validó los datos antes de llegar aquí gracias a StoreEvaluadorRequest.
            // Si hay error (CI duplicado, email repetido), Laravel lanza una excepción
            // y devuelve automáticamente el JSON de error 422.

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

    /**
     * GET /api/v1/evaluadores/{id}
     * Obtiene el detalle de un evaluador por su ID de Usuario.
     */
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

    /**
     * POST /api/v1/evaluadores/ci/{ci}/asignaciones
     * Agrega nuevas áreas/niveles a un evaluador existente.
     */
    public function addAsignaciones(Request $request, string $ci): JsonResponse
    {
        // Validación manual aquí ya que es un payload específico
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

            // Si el error es "Usuario no existe", devolvemos 404, sino 500
            $status = str_contains($e->getMessage(), 'no existe') ? 404 : 500;

            return response()->json([
                'success' => false,
                'message' => 'No se pudieron agregar las asignaciones.',
                'error'   => $e->getMessage()
            ], $status);
        }
    }
}
