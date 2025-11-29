<?php

namespace Database\Seeders;

use App\Model\Area;
use App\Model\AreaOlimpiada;
use App\Model\Olimpiada;
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
            ['nombre_area' => 'Historia'],
            ['nombre_area' => 'Geografía'],
            ['nombre_area' => 'Literatura'],
            ['nombre_area' => 'Arte'],
            ['nombre_area' => 'Educación Física'],
        ];

        Area::insert($areasData);
        $this->command->info('Áreas base creadas exitosamente.');

        $olimpiada = Olimpiada::first();

        if (!$olimpiada) {
            $this->command->warn('No se encontraron olimpiadas. Ejecuta el seeder de Olimpiadas primero.');
            return;
        }

        $todasLasAreas = Area::all();
        $relaciones = [];
        foreach ($todasLasAreas as $area) {
            $relaciones[] = [
                'id_area' => $area->id_area,
                'id_olimpiada' => $olimpiada->id_olimpiada,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        AreaOlimpiada::insert($relaciones);
        $this->command->info('Relaciones entre áreas y olimpiada creadas exitosamente.');
    }
}