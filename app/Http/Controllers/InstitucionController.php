<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Http\Requests\Institucion\StoreInstitucionRequest;
use App\Services\InstitucionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class InstitucionController extends Controller
{
    public function __construct(
        protected InstitucionService $institucionService
    ) {}

    public function index(): JsonResponse
    {
        try {
            $data = $this->institucionService->getAll();
            return response()->json(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $data = $this->institucionService->findById($id);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function store(StoreInstitucionRequest $request): JsonResponse
    {
        try {
            $data = $this->institucionService->create($request->validated());
            return response()->json(['success' => true, 'message' => 'Institución creada', 'data' => $data], 201);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'nombre' => 'sometimes|string|max:250|unique:institucion,nombre,' . $id . ',id_institucion'
        ]);

        try {
            $data = $this->institucionService->update($id, $request->all());
            return response()->json(['success' => true, 'message' => 'Institución actualizada', 'data' => $data]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->institucionService->delete($id);
            return response()->json(['success' => true, 'message' => 'Institución eliminada']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
