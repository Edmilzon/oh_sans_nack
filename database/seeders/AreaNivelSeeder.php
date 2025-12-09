<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Model\Area;
use App\Model\Nivel;
use App\Model\Olimpiada;
use App\Model\AreaOlimpiada;
use App\Model\AreaNivel;
use App\Model\GradoEscolaridad;

class AreaNivelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $olimpiada = Olimpiada::where('gestion', date('Y'))->first()
                     ?? Olimpiada::latest('id_olimpiada')->first();

        if (!$olimpiada) {
            $this->command->error('❌ No se encontró una olimpiada activa.');
            return;
        }

        $this->command->info("Configurando niveles para: {$olimpiada->nombre}");

        $areas = Area::all();
        $niveles = Nivel::all();
        $grados = GradoEscolaridad::all();

        if ($areas->isEmpty() || $niveles->isEmpty() || $grados->isEmpty()) {
            $this->command->warn('⚠️ Faltan áreas, niveles o grados base.');
            return;
        }


        foreach ($areas as $area) {
            $areaOlimpiada = AreaOlimpiada::firstOrCreate([
                'id_area' => $area->id_area,
                'id_olimpiada' => $olimpiada->id_olimpiada
            ]);

            $numNiveles = match ($area->nombre) {
                'Matemáticas' => 3,
                'Física', 'Química' => 2,
                default => 1
            };

            for ($i = 0; $i < $numNiveles; $i++) {
                $nivel = $niveles->slice($i, 1)->first();

                if ($nivel) {
                    $areaNivel = AreaNivel::firstOrCreate([
                        'id_area_olimpiada' => $areaOlimpiada->id_area_olimpiada,
                        'id_nivel' => $nivel->id_nivel
                    ], [
                        'es_activo' => true
                    ]);

                    $gradosParaNivel = [];

                    if ($i == 0) {
                        $g1 = $grados->first(fn($g) => stripos($g->nombre, '1ro') !== false);
                        $g2 = $grados->first(fn($g) => stripos($g->nombre, '2do') !== false);
                        if ($g1) $gradosParaNivel[] = $g1->id_grado_escolaridad;
                        if ($g2) $gradosParaNivel[] = $g2->id_grado_escolaridad;
                    } elseif ($i == 1) {
                        $g3 = $grados->first(fn($g) => stripos($g->nombre, '3ro') !== false);
                        $g4 = $grados->first(fn($g) => stripos($g->nombre, '4to') !== false);
                        if ($g3) $gradosParaNivel[] = $g3->id_grado_escolaridad;
                        if ($g4) $gradosParaNivel[] = $g4->id_grado_escolaridad;
                    } elseif ($i == 2) {
                        $g5 = $grados->first(fn($g) => stripos($g->nombre, '5to') !== false);
                        $g6 = $grados->first(fn($g) => stripos($g->nombre, '6to') !== false);
                        if ($g5) $gradosParaNivel[] = $g5->id_grado_escolaridad;
                        if ($g6) $gradosParaNivel[] = $g6->id_grado_escolaridad;
                    }

                    if (!empty($gradosParaNivel)) {

                        $areaNivel->gradosEscolaridad()->syncWithoutDetaching($gradosParaNivel);
                    }
                }
            }
        }

        $this->command->info('✅ Niveles por área y sus grados configurados exitosamente.');
    }
}
