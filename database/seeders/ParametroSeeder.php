<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\Parametro;
use App\Model\AreaNivel;

class ParametroSeeder extends Seeder
{
    public function run(): void
    {
        // ID que intentamos buscar inicialmente
        $idTarget = 11;

        // Buscamos el AreaNivel cargando las relaciones necesarias para mostrar info
        // Nota: En V8 la relación es AreaNivel -> AreaOlimpiada -> Area
        $areaNivel = AreaNivel::with(['areaOlimpiada.area', 'nivel'])->find($idTarget);

        // Si no existe el ID 11, usamos el primero que encontremos para asegurar que se cree algo
        if (!$areaNivel) {
            $areaNivel = AreaNivel::with(['areaOlimpiada.area', 'nivel'])->first();
        }

        if ($areaNivel) {
            Parametro::firstOrCreate(
                ['id_area_nivel' => $areaNivel->id_area_nivel],
                [
                    // Nombres de columnas corregidos según migración V8
                    'nota_min_aprox_param' => 51.00,
                    'cantidad_maxi_param' => 100,
                ]
            );
            
            $nombreArea = $areaNivel->areaOlimpiada->area->nombre_area;
            $nombreNivel = $areaNivel->nivel->nombre_nivel;

            $this->command->info("✅ Parámetro de calificación configurado para: {$nombreArea} - {$nombreNivel} (ID: {$areaNivel->id_area_nivel})");
        } else {
            $this->command->warn("⚠️ No hay registros en 'area_nivel'. Ejecuta AreasEvaluadoresSeeder primero.");
        }
    }
}