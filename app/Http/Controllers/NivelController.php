<?php

namespace App\Http\Controllers;

use App\Services\NivelService;
use App\Model\Nivel;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class NivelController extends Controller {
    protected $nivelService;

    public function __construct(NivelService $nivelService){
        $this->nivelService = $nivelService;
    }
    
    public function index() {
        $niveles = $this->nivelService->getNivelList();
        return response()->json($niveles);
    }

    public function store(Request $request) {
        return DB::transaction(function() use ($request) {

            $validatedData = $request->validate([
                'nombre' => 'required|string'
            ]);
    
            $existeNivel = Nivel::where('nombre', $validatedData['nombre'])->first();
            if ($existeNivel) {
                return response()->json([
                    'error' => 'El nombre del nivel ya se encuentra registrado'
                ], 422);
            }

            // Crear el nivel
            $nivel = $this->nivelService->createNewNivel($validatedData);

            return response()->json([
                'nivel' => $nivel
            ], 201);
        });
    }

     public function show($id): JsonResponse
    {
        try {
            $nivel = $this->nivelService->findById($id);

            if (!$nivel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nivel no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $nivel
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el nivel: ' . $e->getMessage()
            ], 500);
        }
    }
}
