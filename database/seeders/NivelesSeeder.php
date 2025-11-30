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
<<<<<<< HEAD
        DB::table('nivel')->insert([
            ['nombre_nivel' => '1ro de Secundaria', 'created_at' => now(), 'updated_at' => now()],
            ['nombre_nivel' => '2do de Secundaria', 'created_at' => now(), 'updated_at' => now()],
            ['nombre_nivel' => '3ro de Secundaria', 'created_at' => now(), 'updated_at' => now()],
            ['nombre_nivel' => '4to de Secundaria', 'created_at' => now(), 'updated_at' => now()],
            ['nombre_nivel' => '5to de Secundaria', 'created_at' => now(), 'updated_at' => now()],
            ['nombre_nivel' => '6to de Secundaria', 'created_at' => now(), 'updated_at' => now()],
            ['nombre_nivel' => 'Guacamayo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre_nivel' => 'Tapir', 'created_at' => now(), 'updated_at' => now()],
            ['nombre_nivel' => 'Cóndor', 'created_at' => now(), 'updated_at' => now()],
        ]);
=======
        // Limpiar tabla antes de insertar para evitar duplicados en desarrollo
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('nivel')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1

        $now = now();

        // Insertamos niveles genéricos o específicos según tu modelo académico actual.
        // Nota: Ajustado a 'nombre_nivel' según migración V8.
        $niveles = [
            ['nombre_nivel' => '1ro de Secundaria', 'created_at' => $now, 'updated_at' => $now],
            ['nombre_nivel' => '2do de Secundaria', 'created_at' => $now, 'updated_at' => $now],
            ['nombre_nivel' => '3ro de Secundaria', 'created_at' => $now, 'updated_at' => $now],
            ['nombre_nivel' => '4to de Secundaria', 'created_at' => $now, 'updated_at' => $now],
            ['nombre_nivel' => '5to de Secundaria', 'created_at' => $now, 'updated_at' => $now],   
            ['nombre_nivel' => '6to de Secundaria', 'created_at' => $now, 'updated_at' => $now], 
            ['nombre_nivel' => 'Bufeo', 'created_at' => $now, 'updated_at' => $now],
            ['nombre_nivel' => 'Guacamayo', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('nivel')->insert($niveles);

        $this->command->info('✅ Niveles académicos (Primaria, Secundaria) creados exitosamente.');
    }
}