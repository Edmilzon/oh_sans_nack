<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GradoEscolaridadSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // Limpiar tabla antes de insertar (opcional)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('grado_escolaridad')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $grados = [
            ['nombre' => '1ro de Secundaria', 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => '2do de Secundaria', 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => '3ro de Secundaria', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('grado_escolaridad')->insert($grados);

        $this->command->info('✅ Grados de escolaridad (1ro a 3ro de Secundaria) insertados correctamente.');
    }
}
