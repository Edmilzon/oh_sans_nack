<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NivelesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::table('nivel')->insert([
            ['nombre' => '1ro de Secundaria', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => '2do de Secundaria', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => '3ro de Secundaria', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => '4to de Secundaria', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => '5to de Secundaria', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => '6to de Secundaria', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Guacamayo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Tapir', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Cóndor', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->command->info('Niveles base creados exitosamente.');
    }
}
