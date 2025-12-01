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
        // 1. Obtener la Olimpiada actual
        $olimpiada = Olimpiada::where('gestion', date('Y'))->first()
                     ?? Olimpiada::latest('id_olimpiada')->first();

        if (!$olimpiada) {
            $this->command->error('❌ No se encontró una olimpiada activa.');
            return;
        }

        $this->command->info("Configurando niveles para: {$olimpiada->nombre}");

        // 2. Obtener datos base
        $areas = Area::all();
        $niveles = Nivel::all();
        $grados = GradoEscolaridad::all();

        if ($areas->isEmpty() || $niveles->isEmpty() || $grados->isEmpty()) {
            $this->command->warn('⚠️ Faltan áreas, niveles o grados base.');
            return;
        }

        // 3. Definir la lógica de asignación

        foreach ($areas as $area) {
            // A. Asegurar que existe la relación Area-Olimpiada
            // Usamos firstOrCreate para no duplicar si ya se corrió otro seeder
            $areaOlimpiada = AreaOlimpiada::firstOrCreate([
                'id_area' => $area->id_area,
                'id_olimpiada' => $olimpiada->id_olimpiada
            ]);

            // Determinar cuántos niveles asignar a esta área (Lógica de negocio simulada)
            $numNiveles = match ($area->nombre) {
                'Matemáticas' => 3,       // 3 Niveles
                'Física', 'Química' => 2, // 2 Niveles
                default => 1              // 1 Nivel para el resto
            };

            // B. Crear AreaNivel y asignar Grados
            for ($i = 0; $i < $numNiveles; $i++) {
                // Obtener el nivel correspondiente (Nivel 1, Nivel 2, etc.)
                // Asumimos que los niveles están ordenados o tomamos los primeros disponibles
                $nivel = $niveles->slice($i, 1)->first();

                if ($nivel) {
                    $areaNivel = AreaNivel::firstOrCreate([
                        'id_area_olimpiada' => $areaOlimpiada->id_area_olimpiada,
                        'id_nivel' => $nivel->id_nivel
                    ], [
                        'es_activo' => true
                    ]);

                    // C. Asignar Grados Escolares a este Nivel (Tabla Pivote area_nivel_grado)
                    // Lógica de ejemplo para distribuir grados:
                    // Nivel 1 -> 1ro y 2do de Secundaria
                    // Nivel 2 -> 3ro y 4to de Secundaria
                    // Nivel 3 -> 5to y 6to de Secundaria

                    $gradosParaNivel = [];

                    // Buscamos grados por nombre (Ajusta los nombres según tu GradoEscolaridadSeeder)
                    if ($i == 0) { // Nivel 1
                        $g1 = $grados->first(fn($g) => stripos($g->nombre, '1ro') !== false);
                        $g2 = $grados->first(fn($g) => stripos($g->nombre, '2do') !== false);
                        if ($g1) $gradosParaNivel[] = $g1->id_grado_escolaridad;
                        if ($g2) $gradosParaNivel[] = $g2->id_grado_escolaridad;
                    } elseif ($i == 1) { // Nivel 2
                        $g3 = $grados->first(fn($g) => stripos($g->nombre, '3ro') !== false);
                        $g4 = $grados->first(fn($g) => stripos($g->nombre, '4to') !== false);
                        if ($g3) $gradosParaNivel[] = $g3->id_grado_escolaridad;
                        if ($g4) $gradosParaNivel[] = $g4->id_grado_escolaridad;
                    } elseif ($i == 2) { // Nivel 3
                        $g5 = $grados->first(fn($g) => stripos($g->nombre, '5to') !== false);
                        $g6 = $grados->first(fn($g) => stripos($g->nombre, '6to') !== false);
                        if ($g5) $gradosParaNivel[] = $g5->id_grado_escolaridad;
                        if ($g6) $gradosParaNivel[] = $g6->id_grado_escolaridad;
                    }

                    if (!empty($gradosParaNivel)) {
                        // Usamos la relación definida en tu modelo AreaNivel:
                        // public function gradosEscolaridad() { return $this->belongsToMany(...) }
                        // Asegúrate de tener esta relación en AreaNivel.php:
                        // return $this->belongsToMany(GradoEscolaridad::class, 'area_nivel_grado', 'id_area_nivel', 'id_grado_escolaridad');

                        // Si no tienes la relación belongsToMany en AreaNivel, usa DB::table para insertar en la pivote
                        // DB::table('area_nivel_grado')->insertOrIgnore(...);

                        // Pero como estamos usando modelos, lo ideal es:
                        // $areaNivel->gradosEscolaridad()->syncWithoutDetaching($gradosParaNivel);

                        // Dado que tu modelo AreaNivel subido tiene:
                        // public function gradosEscolaridad() { return $this->belongsToMany(GradoEscolaridad::class, 'area_nivel_grado', 'id_area_nivel', 'id_grado_escolaridad'); }
                        // ESTO ES CORRECTO.

                        $areaNivel->gradosEscolaridad()->syncWithoutDetaching($gradosParaNivel);
                    }
                }
            }
        }

        $this->command->info('✅ Niveles por área y sus grados configurados exitosamente.');
    }
}
