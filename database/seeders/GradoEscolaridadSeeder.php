<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\GradoEscolaridad;

class GradoEscolaridadSeeder extends Seeder
{
    public function run(): void
    {
        $grados = [
            '1ro de Secundaria',
            '2do de Secundaria',
            '3ro de Secundaria',
            '4to de Secundaria',
            '5to de Secundaria',
            '6to de Secundaria',
        ];

        $this->command->info('Verificando grados de escolaridad...');

        foreach ($grados as $nombre) {
            GradoEscolaridad::firstOrCreate([
                'nombre' => $nombre
            ]);
        }

        $this->command->info('✅ Grados de escolaridad (1ro a 6to) listos.');
    }
}
