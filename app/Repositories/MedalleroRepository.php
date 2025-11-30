<?php

namespace App\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Model\ParametroMedallero;

class MedalleroRepository
{
    /**
     * Obtiene las áreas asignadas a un responsable en la gestión actual.
     */
    public function getAreaPorResponsable(int $idResponsable): Collection
    {
        $gestionActual = date('Y');

        return DB::table('responsable_area')
            ->join('area_olimpiada', 'responsable_area.id_area_olimpiada', '=', 'area_olimpiada.id_area_olimpiada')
            ->join('area', 'area_olimpiada.id_area', '=', 'area.id_area')
            ->join('olimpiada', 'area_olimpiada.id_olimpiada', '=', 'olimpiada.id_olimpiada')
            // Columnas corregidas y alias para compatibilidad
            ->select('area.id_area', 'area.nombre_area as nombre', 'olimpiada.gestion_olimp as gestion')
            ->where('responsable_area.id_usuario', $idResponsable)
            ->where('olimpiada.gestion_olimp', $gestionActual) // Columna corregida
            ->distinct()
            ->orderBy('area.nombre_area') // Columna corregida
            ->get();
    }

    /**
     * Obtiene los niveles asociados a un área en la gestión actual, incluyendo
     * los parámetros de medallero si existen.
     */
    public function getNivelesPorArea(int $idArea): Collection
    {
        $gestionActual = date('Y');

        $niveles = DB::table('area_nivel')
            ->join('nivel', 'area_nivel.id_nivel', '=', 'nivel.id_nivel')
            // Navegación a olimpiada a través de area_olimpiada
            ->join('area_olimpiada', 'area_nivel.id_area_olimpiada', '=', 'area_olimpiada.id_area_olimpiada')
            ->join('olimpiada', 'area_olimpiada.id_olimpiada', '=', 'olimpiada.id_olimpiada')
            ->leftJoin('param_medallero', 'area_nivel.id_area_nivel', '=', 'param_medallero.id_area_nivel')
            ->select(
                'area_nivel.id_area_nivel',
                'nivel.id_nivel',
                'nivel.nombre_nivel as nombre_nivel', // Columna corregida
                'olimpiada.gestion_olimp as gestion', // Columna corregida
                'param_medallero.oro_pa_med as oro', // Columna corregida + Alias
                'param_medallero.plata_pa_med as plata', // Columna corregida + Alias
                'param_medallero.bronce_pa_med as bronce', // Columna corregida + Alias
                'param_medallero.mencion_pa_med as menciones' // Columna corregida + Alias
            )
            ->where('area_olimpiada.id_area', $idArea) // Se accede a id_area a través de area_olimpiada
            ->where('olimpiada.gestion_olimp', $gestionActual) // Columna corregida
            ->where('area_nivel.es_activo_area_nivel', true) // Columna corregida
            ->orderBy('nivel.id_nivel')
            ->get();

        return $niveles->map(function ($nivel) {
            // Se asume que si no hay 'oro' es porque el parámetro de medallero no existe
            if ($nivel->oro === null) {
                unset($nivel->oro, $nivel->plata, $nivel->bronce, $nivel->menciones);
            }
            return $nivel;
        });
    }

    /**
     * Inserta los parámetros del medallero para varios niveles.
     */
    public function insertarMedallero(array $niveles): array
    {
        $resultados = [];

        foreach ($niveles as $nivel) {
            $infoNivel = DB::table('area_nivel')
                ->join('nivel', 'area_nivel.id_nivel', '=', 'nivel.id_nivel')
                ->select('nivel.nombre_nivel as nombre_nivel') // Columna corregida
                ->where('area_nivel.id_area_nivel', $nivel['id_area_nivel'])
                ->first();

            $nombreNivel = $infoNivel->nombre_nivel ?? 'Desconocido';

            // Columnas corregidas para verificación de existencia
            $existente = DB::table('param_medallero')
                ->where('id_area_nivel', $nivel['id_area_nivel'])
                ->first();

            if ($existente) {
                // Columnas corregidas para el mensaje de resultado
                $totalExistente = $existente->oro_pa_med + $existente->plata_pa_med + $existente->bronce_pa_med + $existente->mencion_pa_med;

                $resultados[] = sprintf(
                    "Nivel %s ya tiene registrado medallas para la gestion %s. Oro: %d, Plata: %d, Bronce: %d, Menciones: %d",
                    $nombreNivel,
                    date('Y'), // Usamos el año actual para el mensaje
                    $existente->oro_pa_med,
                    $existente->plata_pa_med,
                    $existente->bronce_pa_med,
                    $existente->mencion_pa_med
                );

                continue;
            }

            // Columnas corregidas para la inserción
            DB::table('param_medallero')->insert([
                'id_area_nivel' => $nivel['id_area_nivel'],
                'oro_pa_med' => $nivel['oro'], // Columna corregida
                'plata_pa_med' => $nivel['plata'], // Columna corregida
                'bronce_pa_med' => $nivel['bronce'], // Columna corregida
                'mencion_pa_med' => $nivel['menciones'], // Columna corregida
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $resultados[] = "Nivel {$nombreNivel} insertado correctamente.";
        }

        return $resultados;
    }
}
