<?php

namespace App\Http\Controllers;

use App\Services\OlimpiadaService;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class OlimpiadaController extends Controller
{
    protected $olimpiadaService;

    public function __construct(OlimpiadaService $olimpiadaService)
    {
        $this->olimpiadaService = $olimpiadaService;
    }

    public function olimpiadasAnteriores()
    {
        try {
            $olimpiadas = $this->olimpiadaService->obtenerOlimpiadasAnteriores();
            
            return response()->json([
                'success' => true,
                'data' => $olimpiadas,
                'message' => 'Olimpiadas anteriores obtenidas correctamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las olimpiadas anteriores: ' . $e->getMessage()
            ], 500);
        }
    }

    public function olimpiadaActual()
    {
        try {
            $olimpiada = $this->olimpiadaService->obtenerOlimpiadaActual();
            
            return response()->json([
                'success' => true,
                'data' => $olimpiada,
                'message' => 'Olimpiada actual obtenida correctamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la olimpiada actual: ' . $e->getMessage()
            ], 500);
        }
    }

    public function gestiones()
    {
        try {
            $gestiones = $this->olimpiadaService->obtenerGestiones();
            
            return response()->json([
                'success' => true,
                'data' => $gestiones,
                'message' => 'Gestiones obtenidas correctamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las gestiones: ' . $e->getMessage()
            ], 500);
        }
    }
}