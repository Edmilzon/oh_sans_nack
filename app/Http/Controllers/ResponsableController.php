<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\Responsable\StoreResponsableRequest;
use App\Services\ResponsableService;
use App\Model\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ResponsableController extends Controller
{
    public function __construct(
        protected ResponsableService $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $query = Usuario::query();
            $query->join('persona', 'usuario.id_persona', '=', 'persona.id_persona')
                  ->select('usuario.*', 'persona.nombre', 'persona.apellido', 'persona.ci', 'persona.telefono');

            $query->whereHas('roles', function($q) {
                $q->where('nombre', 'Responsable Area');
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
                    $q->where('nombre', 'Responsable Area')
                      ->where('usuario_rol.id_olimpiada', $olimpiadaId);
                });
            }

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
                'data'    => $responsables
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(StoreResponsableRequest $request): JsonResponse
    {
        try {
            $result = $this->service->createResponsable($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Responsable registrado exitosamente.',
                'data'    => $result
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creando responsable: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $responsable = $this->service->getById($id);
            if (!$responsable) return response()->json(['success' => false, 'message' => 'No encontrado'], 404);
            return response()->json(['success' => true, 'data' => $responsable]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function updateByCi(Request $request, string $ci): JsonResponse
    {
        $request->validate([
            'nombre' => 'sometimes|string|max:50',
            'apellido' => 'sometimes|string|max:50',
        ]);

        try {
            $result = $this->service->updateResponsable($ci, $request->all());
            return response()->json([
                'success' => true,
                'message' => 'Responsable actualizado correctamente.',
                'data'    => $result
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function addAreas(Request $request, string $ci): JsonResponse
    {
        $request->validate([
            'id_olimpiada' => 'required|integer|exists:olimpiada,id_olimpiada',
            'areas'        => 'required|array|min:1',
            'areas.*'      => 'integer|exists:area,id_area'
        ]);

        try {
            $result = $this->service->addAreasToResponsable($ci, $request->id_olimpiada, $request->areas);
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

    public function getGestionesByCi(string $ci): JsonResponse
    {
        try {
            $gestiones = $this->service->getGestionesByCi($ci);
            return response()->json($gestiones);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getAreasByCiAndGestion(string $ci, string $gestion): JsonResponse
    {
        try {
            $areas = $this->service->getAreasByCiAndGestion($ci, $gestion);
            return response()->json($areas);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getOcupadasEnGestionActual(): JsonResponse
    {
        try {
            $areas = $this->service->getAreasOcupadasEnGestionActual();
            return response()->json([
                'success' => true,
                'data'    => $areas
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function areasConNivelesPorOlimpiadaActual(int $idUsuario): JsonResponse
    {
        $data = $this->service->obtenerAreasConNiveles($idUsuario);

        return response()->json($data);
    }
}
