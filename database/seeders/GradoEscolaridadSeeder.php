<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\GradoEscolaridad;

class GradoEscolaridadSeeder extends Seeder
{
    public function run(): void
    {
        // Lista estándar completa de grados
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
            // firstOrCreate: Si existe, lo deja; si no, lo crea.
            // Esto evita problemas de FK si intentas truncar una tabla en uso.
            GradoEscolaridad::firstOrCreate([
                'nombre' => $nombre
            ]);
        }

        $this->command->info('✅ Grados de escolaridad (1ro a 6to) listos.');
    }
}
