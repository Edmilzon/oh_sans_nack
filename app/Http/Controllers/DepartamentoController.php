<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\Departamento\StoreDepartamentoRequest;
use App\Services\DepartamentoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class DepartamentoController extends Controller
{
    public function __construct(
        protected DepartamentoService $departamentoService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $departamentos = $this->departamentoService->listarDepartamentos();
            return response()->json($departamentos);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al obtener departamentos'], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $departamento = $this->departamentoService->obtenerDepartamento($id);
            return response()->json($departamento);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function store(StoreDepartamentoRequest $request): JsonResponse
    {
        try {
            $departamento = $this->departamentoService->crearDepartamento($request->validated());
            return response()->json([
                'message' => 'Departamento creado exitosamente',
                'data' => $departamento
            ], 201);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al crear departamento'], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        // Validación inline simple para update (puedes crear un FormRequest separado si prefieres)
        $request->validate([
            'nombre' => 'sometimes|string|max:20|unique:departamento,nombre,' . $id . ',id_departamento'
        ]);

        try {
            $departamento = $this->departamentoService->actualizarDepartamento($id, $request->all());
            return response()->json([
                'message' => 'Departamento actualizado correctamente',
                'data' => $departamento
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->departamentoService->eliminarDepartamento($id);
            return response()->json(['message' => 'Departamento eliminado correctamente']);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
