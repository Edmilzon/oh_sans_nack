<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Model\Olimpiada; // Namespace correcto

class OlimpiadaSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Olimpiada::truncate();
        Schema::enableForeignKeyConstraints();

        // 2. Variables dinámicas
        $anioActual = date('Y');
        $anioPasado = $anioActual - 1;

        // 3. Crear Olimpiadas usando Eloquent
        $olimpiadas = [
            [
                'nombre'  => "Olimpiada Científica $anioActual (Gestión Actual)",
                'gestion' => (string) $anioActual,
                'estado'  => true,
            ]
        ];

        foreach ($olimpiadas as $data) {
            Olimpiada::firstOrCreate(
                ['gestion' => $data['gestion']],
                $data
            );
        }

        $this->command->info("Olimpiadas generadas. Gestión activa: $anioActual");
    }
}
