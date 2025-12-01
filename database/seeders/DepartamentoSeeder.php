<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\Departamento;

class DepartamentoSeeder extends Seeder
{
    public function run(): void
    {
        $departamentos = [
            'La Paz',
            'Cochabamba',
            'Santa Cruz',
            'Oruro',
            'Potosí',
            'Chuquisaca',
            'Tarija',
            'Beni',
            'Pando',
        ];

        $this->command->info('Verificando departamentos...');

        foreach ($departamentos as $nombre) {
            // firstOrCreate verifica si existe por nombre; si no, lo crea.
            Departamento::firstOrCreate([
                'nombre' => $nombre
            ]);
        }

        $this->command->info('✅ Departamentos de Bolivia listos.');
    }
}
