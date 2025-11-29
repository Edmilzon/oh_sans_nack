<?php

namespace App\Http\Controllers;

use App\Services\GradoEscolaridadService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;

class GradoEscolaridadController extends Controller
{
    protected $gradoEscolaridadService;

    public function __construct(GradoEscolaridadService $gradoEscolaridadService)
    {
        $this->gradoEscolaridadService = $gradoEscolaridadService;
    }

    public function index(): JsonResponse
    {
        try {
            $grados = $this->gradoEscolaridadService->getAll();

            return response()->json([
                'success' => true,
                'data' => $grados
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los grados de escolaridad: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $grado = $this->gradoEscolaridadService->findById($id);

            if (!$grado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grado de escolaridad no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $grado
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el grado de escolaridad: ' . $e->getMessage()
            ], 500);
        }
    }
}