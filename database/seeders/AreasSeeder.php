<?php

namespace Database\Seeders;

use App\Model\Area;
use Illuminate\Database\Seeder;

class AreasSeeder extends Seeder
{
    public function run(): void
    {
        $areasData = [
            ['nombre_area' => 'Matemáticas'],
            ['nombre_area' => 'Física'],
            ['nombre_area' => 'Química'],
            ['nombre_area' => 'Biología'],
            ['nombre_area' => 'Informática'],
            // 5 áreas nuevas
            ['nombre_area' => 'Historia'],
            ['nombre_area' => 'Geografía'],
            ['nombre_area' => 'Literatura'],
            ['nombre_area' => 'Arte'],
            ['nombre_area' => 'Educación Física'],
        ];

        foreach ($areasData as $area) {
            Area::firstOrCreate(['nombre_area' => $area['nombre_area']]);
        }

        $this->command->info('✅ Catálogo de Áreas base creado exitosamente.');
    }
}
