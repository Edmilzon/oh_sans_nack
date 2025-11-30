<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\AreaNivel;
use App\Model\GradoEscolaridad;
use Illuminate\Support\Facades\DB;

class NivelGradoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Obtener todos los grados de Secundaria
        // Asumimos que tus grados tienen nombres como "1ro de Secundaria", etc.
        $gradosSecundaria = GradoEscolaridad::where('nombre_grado', 'like', '%Secundaria%')->get();

        if ($gradosSecundaria->isEmpty()) {
            $this->command->warn('No hay grados de secundaria creados.');
            return;
        }

        // 2. Obtener todas las configuraciones de Area-Nivel que sean "Secundaria"
        $areasNivelSecundaria = AreaNivel::whereHas('nivel', function($q) {
            $q->where('nombre_nivel', 'Secundaria');
        })->get();

        if ($areasNivelSecundaria->isEmpty()) {
            $this->command->warn('No hay áreas configuradas para Secundaria.');
            return;
        }

        // 3. Vincular: A cada "Matemáticas Secundaria" le asignamos "1ro, 2do, 3ro... 6to"
        $count = 0;
        foreach ($areasNivelSecundaria as $an) {
            foreach ($gradosSecundaria as $grado) {
                DB::table('nivel_grado')->insertOrIgnore([
                    'id_area_nivel' => $an->id_area_nivel,
                    'id_grado_escolaridad' => $grado->id_grado_escolaridad,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $count++;
            }
        }

        $this->command->info("✅ Se asignaron los 6 grados de secundaria a {$areasNivelSecundaria->count()} áreas ({$count} registros).");
    }
}