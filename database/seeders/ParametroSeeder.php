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
        $olimpiada = Olimpiada::where('gestion', date('Y'))->first()
                     ?? Olimpiada::latest('id_olimpiada')->first();

        if (!$olimpiada) {
            $this->command->error('❌ No se encontró una olimpiada activa.');
            return;
        }

        $areaNiveles = AreaNivel::whereHas('areaOlimpiada', function($q) use ($olimpiada) {
            $q->where('id_olimpiada', $olimpiada->id_olimpiada);
        })->get();

        if ($areaNiveles->isEmpty()) {
            $this->command->warn("⚠️ No hay niveles configurados para la olimpiada '{$olimpiada->nombre}'.");
            return;
        }

        $this->command->info("Configurando parámetros para {$areaNiveles->count()} niveles de la gestión {$olimpiada->gestion}...");

        foreach ($areaNiveles as $an) {
            Parametro::firstOrCreate(
                ['id_area_nivel' => $an->id_area_nivel],
                [
                    'nota_min_aprobacion' => 51.0,
                    'cantidad_maxima'     => 100,
                ]
            );
        }

        $this->command->info('✅ Parámetros asignados correctamente.');
    }
}
