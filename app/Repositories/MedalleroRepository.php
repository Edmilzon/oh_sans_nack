<?php

namespace App\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Model\ParametroMedallero;

class MedalleroRepository
{
    public function getAreaPorResponsable(int $idResponsable): Collection
    {
        $gestionActual = date('Y');

        return DB::table('responsable_area')
            ->join('area_olimpiada', 'responsable_area.id_area_olimpiada', '=', 'area_olimpiada.id_area_olimpiada')
            ->join('area', 'area_olimpiada.id_area', '=', 'area.id_area')
            ->join('olimpiada', 'area_olimpiada.id_olimpiada', '=', 'olimpiada.id_olimpiada')
            ->select('area.id_area', 'area.nombre', 'olimpiada.gestion')
            ->where('responsable_area.id_usuario', $idResponsable)
            ->where('olimpiada.gestion', $gestionActual)
            ->distinct()
            ->orderBy('area.nombre')
            ->get();
    }

    public function getNivelesPorArea(int $idArea): Collection
{
    $gestionActual = date('Y');

    $niveles = DB::table('area_nivel')
        ->join('nivel', 'area_nivel.id_nivel', '=', 'nivel.id_nivel')
        ->join('olimpiada', 'area_nivel.id_olimpiada', '=', 'olimpiada.id_olimpiada')
        ->leftJoin('param_medallero', 'area_nivel.id_area_nivel', '=', 'param_medallero.id_area_nivel')
        ->select(
            'area_nivel.id_area_nivel',
            'nivel.id_nivel',
            'nivel.nombre as nombre_nivel',
            'olimpiada.gestion',
            'param_medallero.oro',
            'param_medallero.plata',
            'param_medallero.bronce',
            'param_medallero.menciones'
        )
        ->where('area_nivel.id_area', $idArea)
        ->where('olimpiada.gestion', $gestionActual)
        ->where('area_nivel.activo', true)
        ->orderBy('nivel.id_nivel')
        ->get();

    return $niveles->map(function($nivel) {
        if ($nivel->oro === null) {
            unset($nivel->oro, $nivel->plata, $nivel->bronce, $nivel->menciones);
        }
        return $nivel;
    });
}



    public function insertarMedallero(array $niveles): array
    {
        $resultados = [];

        foreach ($niveles as $nivel) {
            $infoNivel = DB::table('area_nivel')
                ->join('nivel', 'area_nivel.id_nivel', '=', 'nivel.id_nivel')
                ->select('nivel.nombre as nombre_nivel')
                ->where('area_nivel.id_area_nivel', $nivel['id_area_nivel'])
                ->first();

            $nombreNivel = $infoNivel->nombre_nivel ?? 'Desconocido';

            $existente = DB::table('param_medallero')
                ->where('id_area_nivel', $nivel['id_area_nivel'])
                ->first();

            if ($existente) {
                $totalExistente = $existente->oro + $existente->plata + $existente->bronce + $existente->menciones;

                $resultados[] = sprintf(
                    "Nivel %s ya tiene registrado medallas para la gestion 2025 Oro: %d, Plata: %d, Bronce: %d, Menciones: %d",
                    $nombreNivel,
                    $existente->oro,
                    $existente->plata,
                    $existente->bronce,
                    $existente->menciones
                );

                continue; 
            }
            DB::table('param_medallero')->insert([
                'id_area_nivel' => $nivel['id_area_nivel'],
                'oro' => $nivel['oro'],
                'plata' => $nivel['plata'],
                'bronce' => $nivel['bronce'],
                'menciones' => $nivel['menciones'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $resultados[] = "Nivel {$nombreNivel} insertado correctamente.";
        }

        return $resultados;
    }
}