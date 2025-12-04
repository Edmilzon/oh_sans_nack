<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\Area;
use App\Model\Olimpiada;

class AreasSeeder extends Seeder
{
    public function run(): void
    {
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

        $this->command->info('Verificando áreas...');
        foreach ($areasNombres as $nombre) {
            Area::firstOrCreate(['nombre' => $nombre]);
        }
        $this->command->info('Áreas base listas.');

        $olimpiada = Olimpiada::where('gestion', date('Y'))->first()
                     ?? Olimpiada::latest('id_olimpiada')->first();

        if (!$olimpiada) {
            $this->command->warn('⚠️ No se encontraron olimpiadas. Ejecuta OlimpiadaSeeder primero.');
            return;
        }

        $this->command->info("Asociando todas las áreas a: {$olimpiada->nombre}");

        $areasIds = Area::pluck('id_area');

        $olimpiada->areas()->syncWithoutDetaching($areasIds);

        $this->command->info('✅ Relaciones creadas exitosamente.');
    }
}
