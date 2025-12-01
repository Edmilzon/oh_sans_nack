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
            ],
            [
                'nombre'  => "Olimpiada Científica $anioPasado (Histórico)",
                'gestion' => (string) $anioPasado,
                'estado'  => false,
            ],
            // Puedes agregar un futuro si quieres pruebas
            /*
            [
                'nombre'  => "Olimpiada Científica " . ($anioActual + 1),
                'gestion' => (string) ($anioActual + 1),
                'estado'  => false,
            ],
            */
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
