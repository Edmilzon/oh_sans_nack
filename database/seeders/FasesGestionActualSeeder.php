<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Model\Olimpiada;
use App\Model\AreaNivel;
use App\Model\Fase;

class FasesGestionActualSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('Iniciando seeder para crear fases en la gestión actual...');

            // 1. Obtener la olimpiada actual
            $gestionActual = date('Y');
            $olimpiada = Olimpiada::where('gestion', $gestionActual)->first();

            if (!$olimpiada) {
                $this->command->error("❌ No se encontró la olimpiada para la gestión {$gestionActual}");
                return;
            }

            $this->command->info("✅ Olimpiada actual encontrada: {$olimpiada->nombre}");

            // 2. Obtener algunos area_nivel de la gestión actual
            $areaNiveles = AreaNivel::where('id_olimpiada', $olimpiada->id_olimpiada)
                ->where('activo', true)
                ->take(3)
                ->get();

            if ($areaNiveles->isEmpty()) {
                $this->command->error("❌ No se encontraron relaciones área-nivel activas para la gestión {$gestionActual}");
                $this->command->info("💡 Ejecuta primero: php artisan db:seed --class=AreasEvaluadoresSeeder");
                return;
            }

            $this->command->info("✅ Se encontraron {$areaNiveles->count()} relaciones área-nivel");

            // 3. Crear fases para cada area_nivel
            $fasesCreadas = [];
            
            foreach ($areaNiveles as $areaNivel) {

                $faseClasificatoria = Fase::create([
                    'nombre' => 'Clasificatoria',
                    'orden' => 1,
                    'id_area_nivel' => $areaNivel->id_area_nivel
                ]);
                $fasesCreadas[] = $faseClasificatoria;

                $faseFinal = Fase::create([
                    'nombre' => 'Final',
                    'orden' => 2,
                    'id_area_nivel' => $areaNivel->id_area_nivel
                ]);
                $fasesCreadas[] = $faseFinal;

                $this->command->info("✅ Fases creadas para Área: {$areaNivel->area->nombre}, Nivel: {$areaNivel->nivel->nombre}");
            }

            $this->command->info("🎉 ¡Seeder completado! Se crearon " . count($fasesCreadas) . " fases para la gestión {$gestionActual}");
            $this->command->info("📋 Fases creadas: Clasificatoria y Final para {$areaNiveles->count()} relaciones área-nivel");
        });
    }
}