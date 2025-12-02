<?php

namespace App\Http\Controllers;

// Usamos el namespace base del framework por seguridad
use Illuminate\Routing\Controller;

use App\Http\Requests\Responsable\StoreResponsableRequest;
use App\Services\ResponsableService;
use App\Model\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ResponsableController extends Controller
{
    public function __construct(
        protected ResponsableService $service
    ) {}

    /**
     * GET /api/v1/responsables
     * Buscador Avanzado (Igual que Evaluador)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Usuario::query();

            $query->join('persona', 'usuario.id_persona', '=', 'persona.id_persona')
                  ->select('usuario.*', 'persona.nombre', 'persona.apellido', 'persona.ci', 'persona.telefono');

            // Filtro por Rol Responsable
            $query->whereHas('roles', function($q) {
                $q->where('nombre', 'Responsable Area');
            });

            // Buscador
            if ($search = $request->input('search')) {
                $query->where(function($q) use ($search) {
                    $q->where('persona.nombre', 'like', "%{$search}%")
                      ->orWhere('persona.apellido', 'like', "%{$search}%")
                      ->orWhere('persona.ci', 'like', "%{$search}%")
                      ->orWhere('usuario.email', 'like', "%{$search}%");
                });
            }

            // Filtro por Olimpiada
            if ($olimpiadaId = $request->input('olimpiada_id')) {
                $query->whereHas('roles', function($q) use ($olimpiadaId) {
                    $q->where('nombre', 'Responsable Area')
                      ->where('usuario_rol.id_olimpiada', $olimpiadaId);
                });
            }

            // Ordenamiento
            $sortField = $request->input('sort_by', 'created_at');
            $sortDirection = $request->input('sort_order', 'desc');

            if (in_array($sortField, ['nombre', 'apellido', 'ci'])) {
                $query->orderBy("persona.$sortField", $sortDirection);
            } else {
                $query->orderBy("usuario.$sortField", $sortDirection);
            }

            $responsables = $query->paginate($request->input('per_page', 15));

            return response()->json([
                'success' => true,
                'message' => 'Lista de responsables obtenida.',
                'data'    => $responsables
            ]);

        } catch (\Exception $e) {
            Log::error('Error index responsables: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v1/responsables
     * Registro nuevo con validación estricta.
     */
    public function store(StoreResponsableRequest $request): JsonResponse
    {
        try {
            // Validación automática inyectada
            $data = $request->validated();

            $result = $this->service->createResponsable($data);

            return response()->json([
                'success' => true,
                'message' => 'Responsable registrado exitosamente.',
                'data'    => $result
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            Log::error('Error creando responsable: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar.',
                'error'   => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * GET /api/v1/responsables/{id}
     */
    public function show($id): JsonResponse
    {
        try {
            $responsable = $this->service->getById($id);

            if (!$responsable) {
                return response()->json(['success' => false, 'message' => 'No encontrado'], 404);
            }

            return response()->json(['success' => true, 'data' => $responsable]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v1/responsables/ci/{ci}/areas
     * (Escenario 3: Agregar áreas a responsable existente)
     */
    public function addAreas(Request $request, string $ci): JsonResponse
    {
        $request->validate([
            'id_olimpiada' => 'required|integer|exists:olimpiada,id_olimpiada',
            'areas'        => 'required|array|min:1',
            'areas.*'      => 'integer|exists:area,id_area'
        ]);

        try {
            $result = $this->service->addAreasToResponsable(
                $ci,
                $request->id_olimpiada,
                $request->areas
            );

            return response()->json([
                'success' => true,
                'message' => 'Áreas asignadas correctamente.',
                'data'    => $result
            ]);

        } catch (\Exception $e) {
            $status = str_contains($e->getMessage(), 'no existe') ? 404 : 500;
            return response()->json(['success' => false, 'message' => $e->getMessage()], $status);
        }
    }
}
