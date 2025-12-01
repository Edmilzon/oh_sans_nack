<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FaseGlobalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('fase_global')->insert([
            [
                'codigo' => 'CONFIG',
                'nombre' => 'Fase de Configuración',
                'orden' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'codigo' => 'EVAL',
                'nombre' => 'Fase de Calificación',
                'orden' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'codigo' => 'FINAL',
                'nombre' => 'Fase Final',
                'orden' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
