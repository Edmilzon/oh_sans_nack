<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GradoEscolaridadSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('grado_escolaridad')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $grados = [
            ['nombre_grado' => '1ro de Secundaria', 'created_at' => $now, 'updated_at' => $now],
            ['nombre_grado' => '2do de Secundaria', 'created_at' => $now, 'updated_at' => $now],
            ['nombre_grado' => '3ro de Secundaria', 'created_at' => $now, 'updated_at' => $now],
            ['nombre_grado' => '4to de Secundaria', 'created_at' => $now, 'updated_at' => $now],
            ['nombre_grado' => '5to de Secundaria', 'created_at' => $now, 'updated_at' => $now],
            ['nombre_grado' => '6to de Secundaria', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('grado_escolaridad')->insert($grados);

        $this->command->info('✅ Grados de escolaridad insertados correctamente.');
    }
}