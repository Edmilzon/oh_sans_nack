<?php

namespace App\Http\Controllers;

use App\Model\Area;
use Illuminate\Http\Request;
use App\Services\AreaService;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use App\Model\AreaOlimpiada;
use App\Model\Olimpiada;

class AreaController extends Controller {

    protected $areaService;

    public function __construct(AreaService $areaService){
        $this-> areaService = $areaService;
    }
    public function index(){
    $areas = $this->areaService->getAreaList();
    return response()->json($areas);
    }

    public function store(Request $request) {
       return DB::transaction(function() use ($request) {

       $validateData = $request->validate([
            'nombre'      => 'required|string',
        ]);


        $existeArea = Area::where('nombre', $validateData['nombre'])->first();
        if ($existeArea) {
            return response()->json([
                'error' => 'El nombre del Área se encuentra registrado'
            ], 422);
        }

        $area = $this->areaService->createNewArea($validateData);

        return response()->json([
            'area' => $area
        ], 201);
    });
    }

    public function obtenerAreasGestionActual(): JsonResponse
    {
        try {
            $areas = $this->areaService->getAreasGestionActual();

            return response()->json([
                'success' => true,
                'data' => [
                    'areas' => $areas
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las áreas: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getActualesPlanas(): JsonResponse
    {
        $olimpiada = Olimpiada::latest('id_olimpiada')->first();
        if (!$olimpiada) return response()->json(['success'=>true, 'data'=>[]]);

        $areas = AreaOlimpiada::with('area')
            ->where('id_olimpiada', $olimpiada->id_olimpiada)
            ->get()
            ->pluck('area')
            ->unique('id_area')
            ->map(function($area) {
                return [
                    'id_area' => $area->id_area,
                    'nombre'  => $area->nombre
                ];
            })->values();

        return response()->json([
            'success' => true,
            'message' => 'Áreas obtenidas correctamente',
            'data'    => $areas
        ]);
    }
}
