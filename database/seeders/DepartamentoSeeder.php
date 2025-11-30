<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartamentoSeeder extends Seeder
{
    public function run(): void
    {
        $deps = [
            ['nombre_dep' => 'La Paz'],
            ['nombre_dep' => 'Cochabamba'],
            ['nombre_dep' => 'Santa Cruz'],
            ['nombre_dep' => 'Oruro'],
            ['nombre_dep' => 'Potosí'],
            ['nombre_dep' => 'Chuquisaca'],
            ['nombre_dep' => 'Tarija'],
            ['nombre_dep' => 'Beni'],
            ['nombre_dep' => 'Pando'],
        ];
        
        DB::table('departamento')->insertOrIgnore($deps);
    }
}