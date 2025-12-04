<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\Institucion;

class InstitucionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $instituciones = [
            'Colegio Nacional (Sucre)',
            'Unidad Educativa Santa Cruz 2',
            'Instituto Simón Bolívar',
            'Colegio Bolívar "B"',
            'Colegio La Paz',
            'Colegio Don Bosco',
            'Colegio La Salle',
            'Colegio San Agustín',
            'Colegio Alemán',
            'Instituto Americano',
        ];

        $this->command->info('🏫 Verificando y creando instituciones...');

        foreach ($instituciones as $nombre) {
            Institucion::firstOrCreate([
                'nombre' => $nombre
            ]);
        }

        $this->command->info('✅ Instituciones base creadas exitosamente.');
    }
}
