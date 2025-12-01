<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\GradoEscolaridad\StoreGradoEscolaridadRequest;
use App\Services\GradoEscolaridadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class GradoEscolaridadController extends Controller
{
    public function __construct(
        protected GradoEscolaridadService $gradoService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $grados = $this->gradoService->getAll();
            return response()->json([
                'success' => true,
                'data'    => $grados
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener grados: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $grado = $this->gradoService->findById($id);
            return response()->json([
                'success' => true,
                'data'    => $grado
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function store(StoreGradoEscolaridadRequest $request): JsonResponse
    {
        try {
            $grado = $this->gradoService->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Grado creado correctamente',
                'data'    => $grado
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'nombre' => 'sometimes|string|max:255|unique:grado_escolaridad,nombre,' . $id . ',id_grado_escolaridad'
        ]);

        try {
            $grado = $this->gradoService->update($id, $request->all());
            return response()->json([
                'success' => true,
                'message' => 'Grado actualizado correctamente',
                'data'    => $grado
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->gradoService->delete($id);
            return response()->json([
                'success' => true,
                'message' => 'Grado eliminado correctamente'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
