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
            ['nombre' => 'Matemáticas'],
            ['nombre' => 'Física'],
            ['nombre' => 'Química'],
            ['nombre' => 'Biología'],
            ['nombre' => 'Informática'],
            // 5 áreas nuevas
            ['nombre' => 'Historia'],
            ['nombre' => 'Geografía'],
            ['nombre' => 'Literatura'],
            ['nombre' => 'Arte'],
            ['nombre' => 'Educación Física'],
        ];

        // 1. Insertar todas las áreas en un solo query.
        Area::insert($areasData);
        $this->command->info('Áreas base creadas exitosamente.');

        // Buscar la primera olimpiada existente.
        $olimpiada = Olimpiada::first();

        if (!$olimpiada) {
            $this->command->warn('No se encontraron olimpiadas. Las relaciones en area_olimpiada no se crearán. Ejecuta el seeder de Olimpiadas primero.');
            return;
        }

        $this->command->info("Asociando áreas con la olimpiada: '{$olimpiada->nombre}' (ID: {$olimpiada->id_olimpiada})");

        // 2. Preparar los datos para la tabla pivote.
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

        // 3. Insertar todas las relaciones en un solo query.
        AreaOlimpiada::insert($relaciones);

        $this->command->info('Relaciones entre áreas y olimpiada creadas exitosamente.');
    }
}
