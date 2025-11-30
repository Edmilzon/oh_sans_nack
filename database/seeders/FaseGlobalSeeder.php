<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FaseGlobalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $fases = [
            [
                'codigo_fas_glo' => 'F0_INS',
                'nombre_fas_glo' => 'Etapa de Inscripción',
                'orden_fas_glo'  => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'codigo_fas_glo' => 'F1_CLAS',
                'nombre_fas_glo' => 'Etapa Evaluacion',
                'orden_fas_glo'  => 2,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'codigo_fas_glo' => 'F2_DEP',
                'nombre_fas_glo' => 'Etapa Final',
                'orden_fas_glo'  => 3,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
        ];

        // Usamos upsert para evitar duplicados si el seeder corre dos veces
        // Buscamos por 'codigo_fas_glo' (que debería ser único lógicamente)
        // Si no tienes unique en código, usamos insertOrIgnore o truncate antes.
        
        // Opción segura: Limpiar tabla (solo en desarrollo) o insertOrIgnore
        // DB::table('fase_global')->truncate(); 
        
        foreach ($fases as $fase) {
            DB::table('fase_global')->updateOrInsert(
                ['codigo_fas_glo' => $fase['codigo_fas_glo']], // Condición de búsqueda
                $fase // Datos a insertar/actualizar
            );
        }

        $this->command->info('✅ Fases Globales (Inscripción, Clasificatoria, Departamental, Nacional) creadas.');
    }
}