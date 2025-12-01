<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\Parametro;
use App\Model\AreaNivel;
use App\Model\Olimpiada;

class ParametroSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Obtener Olimpiada Actual
        $olimpiada = Olimpiada::where('gestion', date('Y'))->first()
                     ?? Olimpiada::latest('id_olimpiada')->first();

        if (!$olimpiada) {
            $this->command->error('❌ No se encontró una olimpiada activa.');
            return;
        }

        // 2. Obtener AreaNiveles usando la relación correcta (AreaOlimpiada)
        // Esto le dice a Eloquent: "Busca los AreaNivel que tengan un AreaOlimpiada que pertenezca a esta Olimpiada"
        $areaNiveles = AreaNivel::whereHas('areaOlimpiada', function($q) use ($olimpiada) {
            $q->where('id_olimpiada', $olimpiada->id_olimpiada);
        })->get();

        if ($areaNiveles->isEmpty()) {
            $this->command->warn("⚠️ No hay niveles configurados para la olimpiada '{$olimpiada->nombre}'.");
            return;
        }

        $this->command->info("Configurando parámetros para {$areaNiveles->count()} niveles de la gestión {$olimpiada->gestion}...");

        foreach ($areaNiveles as $an) {
            // 3. Crear Parámetros por defecto
            Parametro::firstOrCreate(
                ['id_area_nivel' => $an->id_area_nivel],
                [
                    'nota_min_aprobacion' => 51.0,
                    'cantidad_maxima'     => 100, // Cupo máximo de clasificados por defecto
                ]
            );
        }

        $this->command->info('✅ Parámetros asignados correctamente.');
    }
}
