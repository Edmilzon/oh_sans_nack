<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Model\Olimpiada;
use App\Model\AreaOlimpiada;
use App\Model\Nivel;
use App\Model\AreaNivel;

class AreaNivelSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // 1. Obtener la Olimpiada Actual
        $olimpiada = Olimpiada::where('gestion_olimp', date('Y'))->first();
        if (!$olimpiada) {
            $olimpiada = Olimpiada::first();
            if (!$olimpiada) {
                $this->command->error('❌ No hay olimpiadas. Ejecuta OlimpiadaSeeder primero.');
                return;
            }
        }

        // 2. Obtener Niveles (Primaria / Secundaria)
        $nivelSecundaria = Nivel::where('nombre_nivel', 'Secundaria')->first();
        $nivelPrimaria = Nivel::where('nombre_nivel', 'Primaria')->first();

        if (!$nivelSecundaria) {
            $this->command->error('❌ No se encontró el nivel Secundaria. Ejecuta NivelesSeeder.');
            return;
        }

        // 3. Obtener las Áreas habilitadas para esta olimpiada
        $areasOlimpiada = AreaOlimpiada::with('area')
            ->where('id_olimpiada', $olimpiada->id_olimpiada)
            ->get();

        if ($areasOlimpiada->isEmpty()) {
            $this->command->error('❌ No hay áreas vinculadas a la olimpiada. Ejecuta AreaOlimpiadaSeeder.');
            return;
        }

        $count = 0;

        // 4. Generar la matriz Área-Nivel
        foreach ($areasOlimpiada as $ao) {
            $nombreArea = $ao->area->nombre_area;

            // LÓGICA DE NEGOCIO:
            // Por defecto, todas las áreas se habilitan para Secundaria.
            // Algunas áreas específicas podrían habilitarse para Primaria también.

            $nivelesHabilitar = [$nivelSecundaria]; // Todos a secundaria

            // Ejemplo: Matemáticas y Ciencias también para Primaria
            if (in_array($nombreArea, ['Matemáticas', 'Ciencias Naturales', 'Robótica'])) {
                if ($nivelPrimaria) {
                    $nivelesHabilitar[] = $nivelPrimaria;
                }
            }

            foreach ($nivelesHabilitar as $nivel) {
                AreaNivel::firstOrCreate(
                    [
                        'id_area_olimpiada' => $ao->id_area_olimpiada,
                        'id_nivel' => $nivel->id_nivel
                    ],
                    [
                        'es_activo_area_nivel' => true,
                        'created_at' => $now,
                        'updated_at' => $now
                    ]
                );
                $count++;
            }
        }

        $this->command->info("✅ Se configuraron {$count} niveles académicos (AreaNivel) para la gestión {$olimpiada->gestion_olimp}.");
    }
}
