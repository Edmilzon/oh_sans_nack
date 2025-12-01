<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\Nivel; // Importamos el modelo correcto

class NivelesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lista mixta de niveles (Académicos y Categorías de Biología/Ecología)
        $niveles = [
            '1ro de Secundaria',
            '2do de Secundaria',
            '3ro de Secundaria',
            '4to de Secundaria',
            '5to de Secundaria',
            '6to de Secundaria',
            // Categorías especiales (Ej: Olimpiada Biología)
            'Guacamayo',
            'Tapir',
            'Cóndor',
        ];

        $this->command->info('Verificando niveles...');

        foreach ($niveles as $nombre) {
            // firstOrCreate busca por nombre; si no existe, lo crea.
            Nivel::firstOrCreate([
                'nombre' => $nombre
            ]);
        }

        $this->command->info('✅ Niveles base creados/verificados exitosamente.');
    }
}
