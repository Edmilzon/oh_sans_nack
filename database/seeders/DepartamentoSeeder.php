<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartamentoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('departamento')->insert([
            ['nombre' => 'La Paz', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Cochabamba', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Santa Cruz', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Oruro', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Potosí', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Chuquisaca', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Tarija', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Beni', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Pando', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}