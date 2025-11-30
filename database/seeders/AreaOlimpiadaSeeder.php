<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\Area;
use App\Model\Olimpiada;
use App\Model\AreaOlimpiada;

class AreaOlimpiadaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Obtener la olimpiada actual (o la primera disponible)
        $olimpiada = Olimpiada::where('gestion_olimp', date('Y'))->first();

        if (!$olimpiada) {
            // Fallback para desarrollo
            $olimpiada = Olimpiada::first();
        }

        if (!$olimpiada) {
            $this->command->error('❌ No se encontró ninguna olimpiada. Ejecuta OlimpiadaSeeder primero.');
            return;
        }

        // 2. Obtener todas las áreas registradas
        $areas = Area::all();
        if ($areas->isEmpty()) {
            $this->command->error('❌ No hay áreas registradas. Ejecuta AreasSeeder primero.');
            return;
        }

        $this->command->info("🔗 Vinculando {$areas->count()} áreas a la olimpiada: {$olimpiada->nombre_olimp}");

        // 3. Crear la relación (Idempotente: no duplica si ya existe)
        foreach ($areas as $area) {
            AreaOlimpiada::firstOrCreate([
                'id_area' => $area->id_area,
                'id_olimpiada' => $olimpiada->id_olimpiada
            ]);
        }

        $this->command->info('✅ Relaciones Area-Olimpiada verificadas/creadas exitosamente.');
    }
}
