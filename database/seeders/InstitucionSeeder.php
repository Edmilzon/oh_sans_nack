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
        // Lista de instituciones educativas
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
            // Puedes agregar más aquí según necesites
        ];

        $this->command->info('🏫 Verificando y creando instituciones...');

        foreach ($instituciones as $nombre) {
            // firstOrCreate verifica si existe por nombre; si no, lo crea.
            Institucion::firstOrCreate([
                'nombre' => $nombre
            ]);
        }

        $this->command->info('✅ Instituciones base creadas exitosamente.');
    }
}
