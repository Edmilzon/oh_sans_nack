<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\Nivel;

class NivelesSeeder extends Seeder
{
    public function run(): void
    {
        $niveles = [
            '1ro de Secundaria',
            '2do de Secundaria',
            '3ro de Secundaria',
            '4to de Secundaria',
            '5to de Secundaria',
            '6to de Secundaria',
            'Guacamayo',
            'Tapir',
            'Cóndor',
        ];

        $this->command->info('Verificando niveles...');

        foreach ($niveles as $nombre) {
            Nivel::firstOrCreate([
                'nombre' => $nombre
            ]);
        }

        $this->command->info('✅ Niveles base creados/verificados exitosamente.');
    }
}
