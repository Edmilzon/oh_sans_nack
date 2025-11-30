<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\Olimpiada;

class OlimpiadaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
<<<<<<< HEAD
        Olimpiada::create([
            'nombre_olimp' => 'Olimpiada Científica Estudiantil',
            'gestion_olimp' => date('Y'),
        ]);
=======
        $gestion = date('Y'); // Año actual (ej: 2025)
>>>>>>> 3941ec078f622a25b39feac36dc616b2346017d1

        // Usamos firstOrCreate para evitar duplicados si corres el seeder varias veces
        $olimpiada = Olimpiada::firstOrCreate(
            ['gestion_olimp' => $gestion], // Condición de búsqueda
            [
                'nombre_olimp' => "Olimpiada Científica $gestion",
                'estado_olimp' => true,
            ]
        );

        $this->command->info("✅ Olimpiada para la gestión {$olimpiada->gestion_olimp} verificada/creada correctamente.");
    }
}