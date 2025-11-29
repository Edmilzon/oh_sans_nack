<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Model\Olimpiada;
use App\Model\AreaNivel;
use App\Model\FaseGlobal;
use App\Model\Competencia;
use App\Model\CronogramaFase;

class FasesGestionActualSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('🔄 Iniciando FasesGestionActualSeeder (Adaptado a V8)...');

            // 1. Obtener la olimpiada actual
            $gestionActual = date('Y');
            $olimpiada = Olimpiada::where('gestion_olimp', $gestionActual)->first();

            if (!$olimpiada) {
                // Fallback para entornos de prueba si no coincide el año
                $olimpiada = Olimpiada::first();
                if (!$olimpiada) {
                    $this->command->error("❌ No se encontró ninguna olimpiada.");
                    return;
                }
                $this->command->warn("⚠️ Usando olimpiada fallback: {$olimpiada->nombre_olimp}");
            } else {
                $this->command->info("✅ Olimpiada actual encontrada: {$olimpiada->nombre_olimp}");
            }

            // 2. Buscar Fases Globales (Catálogo)
            // Usamos los códigos definidos en FaseGlobalSeeder
            $faseClasificatoria = FaseGlobal::where('codigo_fas_glo', 'F1_CLAS')->first();
            $faseFinal = FaseGlobal::where('codigo_fas_glo', 'F3_NAC')->first(); // O 'F2_DEP' según tu lógica

            if (!$faseClasificatoria || !$faseFinal) {
                $this->command->error("❌ Faltan Fases Globales (F1_CLAS o F3_NAC). Ejecuta FaseGlobalSeeder primero.");
                return;
            }

            // 3. Crear Cronograma Global (Fechas Generales)
            // Esto define "Cuándo es la etapa" a nivel sistema
            $cronogramas = [
                [
                    'id_olimpiada' => $olimpiada->id_olimpiada,
                    'id_fase_global' => $faseClasificatoria->id_fase_global,
                    'fecha_inicio' => now()->addDays(1),
                    'fecha_fin' => now()->addDays(10),
                    'estado' => 'En Curso'
                ],
                [
                    'id_olimpiada' => $olimpiada->id_olimpiada,
                    'id_fase_global' => $faseFinal->id_fase_global,
                    'fecha_inicio' => now()->addMonths(1),
                    'fecha_fin' => now()->addMonths(1)->addDays(5),
                    'estado' => 'Pendiente'
                ]
            ];

            foreach ($cronogramas as $crono) {
                CronogramaFase::firstOrCreate(
                    [
                        'id_olimpiada' => $crono['id_olimpiada'], 
                        'id_fase_global' => $crono['id_fase_global']
                    ],
                    $crono
                );
            }
            $this->command->info("✅ Cronograma de Fases Globales actualizado.");

            // 4. Obtener area_nivel de la gestión actual para crear sus exámenes
            // Usamos whereHas para filtrar por la olimpiada correcta a través de area_olimpiada
            $areaNiveles = AreaNivel::whereHas('areaOlimpiada', function($q) use ($olimpiada) {
                $q->where('id_olimpiada', $olimpiada->id_olimpiada);
            })
            ->where('es_activo_area_nivel', true)
            ->with(['areaOlimpiada.area', 'nivel'])
            ->get();

            if ($areaNiveles->isEmpty()) {
                $this->command->error("❌ No hay AreaNivel activos para esta gestión.");
                return;
            }

            $this->command->info("✅ Generando Competencias (Exámenes) para {$areaNiveles->count()} niveles...");

            // 5. Crear Competencias (Exámenes concretos) para cada area_nivel
            // En V8, esto sustituye a la antigua tabla 'fase'
            $count = 0;
            foreach ($areaNiveles as $an) {
                $nombreArea = $an->areaOlimpiada->area->nombre_area;
                $nombreNivel = $an->nivel->nombre_nivel;

                // A. Crear Competencia Clasificatoria
                Competencia::firstOrCreate([
                    'id_fase_global' => $faseClasificatoria->id_fase_global,
                    'id_area_nivel' => $an->id_area_nivel
                ], [
                    'nombre_examen' => "Examen Clasificatorio - $nombreArea $nombreNivel",
                    'fecha_inicio' => now()->addDays(2), // Dentro del cronograma
                    'fecha_fin' => now()->addDays(3),
                    'ponderacion' => 100.00,
                    'estado_comp' => true
                ]);

                // B. Crear Competencia Final
                Competencia::firstOrCreate([
                    'id_fase_global' => $faseFinal->id_fase_global,
                    'id_area_nivel' => $an->id_area_nivel
                ], [
                    'nombre_examen' => "Examen Final - $nombreArea $nombreNivel",
                    'fecha_inicio' => now()->addMonths(1)->addDays(1),
                    'fecha_fin' => now()->addMonths(1)->addDays(2),
                    'ponderacion' => 100.00,
                    'estado_comp' => false // Aún no activa
                ]);

                $count += 2;
            }

            $this->command->info("🎉 ¡Seeder completado! Se configuraron {$count} competencias en el sistema.");
        });
    }
}