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

    return DB::table('responsable_area AS ra')
        ->join('area_olimpiada AS ao', 'ra.id_area_olimpiada', '=', 'ao.id_area_olimpiada')
        ->join('area AS a', 'ao.id_area', '=', 'a.id_area')
        ->join('olimpiada AS o', 'ao.id_olimpiada', '=', 'o.id_olimpiada')
        ->select('a.id_area', 'a.nombre AS nombre_area', 'o.gestion')
        ->where('ra.id_usuario', $idResponsable)
        ->where('o.gestion', $gestionActual)
        ->distinct()
        ->orderBy('a.nombre')
        ->get();
}


   public function getNivelesPorArea(int $idArea): Collection
{
    $gestionActual = date('Y');

    $niveles = DB::table('area_nivel AS an')
        ->join('area_olimpiada AS ao', 'an.id_area_olimpiada', '=', 'ao.id_area_olimpiada')
        ->join('nivel AS n', 'an.id_nivel', '=', 'n.id_nivel')
        ->join('olimpiada AS o', 'ao.id_olimpiada', '=', 'o.id_olimpiada')
        ->leftJoin('param_medallero AS pm', 'an.id_area_nivel', '=', 'pm.id_area_nivel')
        ->select(
            'an.id_area_nivel',
            'n.id_nivel',
            'n.nombre AS nombre_nivel',
            'o.gestion',
            'pm.oro',
            'pm.plata',
            'pm.bronce',
            'pm.mencion'
        )
        ->where('ao.id_area', $idArea)
        ->where('o.gestion', $gestionActual)
        ->where('an.es_activo', true)
        ->orderBy('n.id_nivel')
        ->get();

    return $niveles->map(function($nivel) {
        if ($nivel->oro === null) {
            unset($nivel->oro, $nivel->plata, $nivel->bronce, $nivel->mencion);
        }
        return $nivel;
    });
}
    public function insertarMedallero(array $niveles): array
{
    $resultados = [];

    foreach ($niveles as $nivel) {
        $item = DB::table('param_medallero')
            ->updateOrInsert(
                ['id_area_nivel' => $nivel['id_area_nivel']],
                [
                    'oro' => $nivel['oro'],
                    'plata' => $nivel['plata'],
                    'bronce' => $nivel['bronce'],
                    'mencion' => $nivel['menciones'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

        $resultados[] = [
            'id_area_nivel' => $nivel['id_area_nivel'],
            'mensaje' => 'Guardado correctamente'
        ];
    }

    return $resultados;
    }
}