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
<<<<<<< HEAD
            // 5 áreas nuevas
=======
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1
            ['nombre_area' => 'Historia'],
            ['nombre_area' => 'Geografía'],
            ['nombre_area' => 'Literatura'],
            ['nombre_area' => 'Arte'],
            ['nombre_area' => 'Educación Física'],
<<<<<<< HEAD
=======
            ['nombre_area' => 'Robótica'], // Agregué esta porque la usas en seeders históricos
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1
        ];

        foreach ($areasData as $area) {
            Area::firstOrCreate(['nombre_area' => $area['nombre_area']]);
        }

        $this->command->info('✅ Catálogo de Áreas base creado exitosamente.');
    }
}
