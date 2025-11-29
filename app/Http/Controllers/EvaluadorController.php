<?php

namespace App\Http\Controllers;

use App\Services\EvaluadorService;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class EvaluadorController extends Controller
{
    protected $evaluadorService;

    public function __construct(EvaluadorService $evaluadorService)
    {
        $this->evaluadorService = $evaluadorService;
    }

    /**
     * Registra un nuevo usuario evaluador.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'nombre' => 'required|string|max:255',
                'apellido' => 'required|string|max:255',
                'ci' => 'required|string|unique:usuario,ci',
                'email' => 'required|email|unique:usuario,email',
                'password' => 'required|string|min:8',
                'telefono' => 'nullable|string|max:20',
                'id_olimpiada' => 'required|integer|exists:olimpiada,id_olimpiada',
                'area_nivel_ids' => 'required|array|min:1',
                'area_nivel_ids.*' => ['integer', 'exists:area_nivel,id_area_nivel', function ($attribute, $value, $fail) use ($request) {
                    // Validar que el id_area_nivel pertenezca a la olimpiada proporcionada
                    if (!DB::table('area_nivel')->where('id_area_nivel', $value)->where('id_olimpiada', $request->id_olimpiada)->exists()) {
                        $fail("La asignación con ID {$value} no pertenece a la olimpiada con ID {$request->id_olimpiada}.");
                    }
                }],
            ], [
                'ci.unique' => 'Ya existe un Evaluador registrado con este C.I. y no se realiza el registro.',
                'email.unique' => 'Ya existe un Evaluador registrado con este correo electrónico y no se realiza el registro.',
            ]);

            $evaluadorData = $request->only([
                'nombre', 'apellido', 'ci', 'email', 'password', 
                'telefono', 'id_olimpiada', 'area_nivel_ids'
            ]);

            $result = $this->evaluadorService->createEvaluador($evaluadorData);

            return response()->json([
                'message' => 'Evaluador registrado exitosamente',
                'data' => $result
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al registrar evaluador',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene todos los responsables de área.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $evaluadores = $this->evaluadorService->getAllEvaluadores();
            
            return response()->json([
                'message' => 'Evaluadores obtenidos exitosamente',
                'data' => $evaluadores
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener evaluadores',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene un evaluador específico por ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $evaluador = $this->evaluadorService->getEvaluadorById($id);

            if (!$evaluador) {
                return response()->json([
                    'message' => 'Evaluador no encontrado'
                ], 404);
            }

            return response()->json([
                'message' => 'Evaluador obtenido exitosamente',
                'data' => $evaluador
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener evaluador',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene las gestiones en las que ha trabajado un evaluador por su CI.
     *
     * @param string $ci
     * @return JsonResponse
     */
    public function getGestionesByCi(string $ci): JsonResponse
    {
        try {
            $gestiones = $this->evaluadorService->getGestionesByCi($ci);

            if (empty($gestiones)) {
                return response()->json([
                    'message' => 'No se encontraron gestiones para el evaluador con el CI proporcionado o el usuario no es un evaluador.',
                    'data' => []
                ]);
            }

            return response()->json($gestiones);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las gestiones del evaluador.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene las áreas asignadas a un evaluador para una gestión específica.
     *
     * @param string $ci
     * @param string $gestion
     * @return JsonResponse
     */
    public function getAreasByCiAndGestion(string $ci, string $gestion): JsonResponse
    {
        try {
            $areas = $this->evaluadorService->getAreasByCiAndGestion($ci, $gestion);

            if (empty($areas)) {
                return response()->json([
                    'message' => 'No se encontraron áreas asignadas para el evaluador con el CI y la gestión proporcionados.',
                    'data' => []
                ]);
            }

            return response()->json($areas);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las áreas del evaluador.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualiza un evaluador existente por su CI.
     *
     * @param Request $request
     * @param string $ci
     * @return JsonResponse
     */
    public function updateByCi(Request $request, string $ci): JsonResponse
    {
        $usuario = DB::table('usuario')->where('ci', $ci)->first();

        if (!$usuario) {
            return response()->json(['message' => 'Evaluador no encontrado con el CI proporcionado.'], 404);
        }

        $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'apellido' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:usuario,email,' . $usuario->id_usuario . ',id_usuario',
            'password' => 'sometimes|required|string|min:8',
            'telefono' => 'nullable|string|max:20',
            'id_olimpiada' => 'sometimes|required|integer|exists:olimpiada,id_olimpiada',
            'areas' => 'sometimes|required|array|min:1',
            'areas.*' => ['integer', 'exists:area,id_area', function ($attribute, $value, $fail) use ($request) {
                if ($request->has('id_olimpiada') && !DB::table('area_olimpiada')->where('id_area', $value)->where('id_olimpiada', $request->id_olimpiada)->exists()) {
                    $fail("El área con ID {$value} no está asociada a la olimpiada con ID {$request->id_olimpiada}.");
                }
            }],
        ]);

        try {
            $data = $request->only([
                'nombre', 'apellido', 'email', 'password', 
                'telefono', 'id_olimpiada', 'areas'
            ]);

            $result = $this->evaluadorService->updateEvaluadorByCi($ci, $data);

            return response()->json([
                'message' => 'Evaluador actualizado exitosamente',
                'data' => $result
            ]);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el evaluador',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Añade nuevas áreas a un evaluador existente por su CI.
     *
     * @param Request $request
     * @param string $ci
     * @return JsonResponse
     */
    public function addAreasByCi(Request $request, string $ci): JsonResponse
    {
        $request->validate([
            'id_olimpiada' => 'required|integer|exists:olimpiada,id_olimpiada',
            'areas' => 'required|array|min:1',
            'areas.*' => ['integer', 'exists:area,id_area', function ($attribute, $value, $fail) use ($request) {
                if (!DB::table('area_olimpiada')->where('id_area', $value)->where('id_olimpiada', $request->id_olimpiada)->exists()) {
                    $fail("El área con ID {$value} no está asociada a la olimpiada con ID {$request->id_olimpiada}.");
                }
            }],
        ]);

        try {
            $data = $request->only(['id_olimpiada', 'areas']);
            $result = $this->evaluadorService->addAreasToEvaluadorByCi($ci, $data);

            if (!$result) {
                return response()->json([
                    'message' => 'Evaluador no encontrado con el CI proporcionado.'
                ], 404);
            }

            return response()->json([
                'message' => 'Áreas añadidas exitosamente al evaluador',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al añadir áreas al evaluador',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Añade nuevas asignaciones de área/nivel a un evaluador por su CI.
     *
     * @param Request $request
     * @param string $ci
     * @return JsonResponse
     */
    public function addAsignacionesByCi(Request $request, string $ci): JsonResponse
    {
        try {
            $request->validate([
                'id_olimpiada' => 'required|integer|exists:olimpiada,id_olimpiada',
                'area_nivel_ids' => 'required|array|min:1',
                'area_nivel_ids.*' => ['integer', 'exists:area_nivel,id_area_nivel', function ($attribute, $value, $fail) use ($request) {
                    if (!DB::table('area_nivel')->where('id_area_nivel', $value)->where('id_olimpiada', $request->id_olimpiada)->exists()) {
                        $fail("La asignación con ID {$value} no pertenece a la olimpiada con ID {$request->id_olimpiada}.");
                    }
                }],
            ]);

            $data = $request->only(['id_olimpiada', 'area_nivel_ids']);
            $result = $this->evaluadorService->addAsignacionesToEvaluadorByCi($ci, $data);

            if (!$result) {
                return response()->json([
                    'message' => 'Evaluador no encontrado con el CI proporcionado.'
                ], 404);
            }

            return response()->json([
                'message' => 'Asignaciones añadidas exitosamente al evaluador',
                'data' => $result
            ]);

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al añadir asignaciones al evaluador',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene las áreas y niveles asignados a un evaluador por su ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getAreasNivelesById(int $id): JsonResponse
    {
        try {
            $areasNiveles = $this->evaluadorService->getAreasNivelesByEvaluadorId($id);

            if (empty($areasNiveles)) {
                return response()->json([
                    'message' => 'No se encontraron áreas y niveles asignados para el evaluador con el ID proporcionado.',
                    'data' => []
                ], 404);
            }

            return response()->json($areasNiveles);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las áreas y niveles del evaluador.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
