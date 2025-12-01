<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\Area;
use App\Model\Olimpiada;

class AreasSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Lista de Áreas a crear
        $areasNombres = [
            'Matemáticas',
            'Física',
            'Química',
            'Biología',
            'Informática',
            'Historia',
            'Geografía',
            'Literatura',
            'Arte',
            'Educación Física',
        ];

        // 2. Crear las áreas (Evitando duplicados con firstOrCreate)
        $this->command->info('Verificando áreas...');
        foreach ($areasNombres as $nombre) {
            Area::firstOrCreate(['nombre' => $nombre]);
        }
        $this->command->info('Áreas base listas.');

        // 3. Buscar la Olimpiada objetivo
        // Prioridad: La del año actual. Si no existe, la última creada.
        $olimpiada = Olimpiada::where('gestion', date('Y'))->first()
                     ?? Olimpiada::latest('id_olimpiada')->first();

        if (!$olimpiada) {
            $this->command->warn('⚠️ No se encontraron olimpiadas. Ejecuta OlimpiadaSeeder primero.');
            return;
        }

        $this->command->info("Asociando todas las áreas a: {$olimpiada->nombre}");

        // 4. Llenar la tabla pivote 'area_olimpiada' usando Eloquent
        // Obtenemos todos los IDs de las áreas
        $areasIds = Area::pluck('id_area');

        // Usamos la relación 'areas()' definida en el modelo Olimpiada.
        // syncWithoutDetaching: Agrega las relaciones si no existen, sin borrar las que ya estaban.
        $olimpiada->areas()->syncWithoutDetaching($areasIds);

        $this->command->info('✅ Relaciones creadas exitosamente.');
    }
}
