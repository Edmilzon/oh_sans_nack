<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\ParametroMedallero;
use App\Model\AreaNivel;

class ParametroMedalleroSeeder extends Seeder
{
    public function run(): void
    {
        // Configurar para todas las áreas activas
        $areaNiveles = AreaNivel::where('es_activo_area_nivel', true)->get();

        foreach ($areaNiveles as $an) {
            ParametroMedallero::firstOrCreate(
                ['id_area_nivel' => $an->id_area_nivel],
                [
                    'oro_pa_med' => 1,     // 1 Oro
                    'plata_pa_med' => 2,   // 2 Platas
                    'bronce_pa_med' => 3,  // 3 Bronces
                    'mencion_pa_med' => 5  // 5 Menciones de Honor
                ]
            );
        }

        $this->command->info("✅ Parámetros de medallero configurados para {$areaNiveles->count()} áreas.");
    }
}