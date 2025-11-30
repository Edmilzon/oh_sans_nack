<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Model\Area;
use App\Model\Nivel;
use App\Model\Olimpiada;
use App\Model\Usuario;
use App\Model\GradoEscolaridad;

class AreasEvaluadoresSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // 1️⃣ Verificar que existan grados escolares
        $grados = GradoEscolaridad::all();
        if ($grados->isEmpty()) {
            $this->command->error('❌ No hay grados de escolaridad. Ejecuta primero: php artisan db:seed --class=GradoEscolaridadSeeder');
            return;
        }

        // 2️⃣ Olimpiada del año actual
        $olimpiada = Olimpiada::where('gestion', date('Y'))->first();
        if (!$olimpiada) {
            $this->command->error('❌ No se encontró olimpiada para el año actual.');
            return;
        }

        // 3️⃣ Áreas
        $areas = Area::all();
        if ($areas->isEmpty()) {
            $this->command->error('❌ No hay áreas. Ejecuta AreasSeeder primero.');
            return;
        }

        // 4️⃣ Niveles
        $niveles = Nivel::all();
        if ($niveles->isEmpty()) {
            $this->command->error('❌ No hay niveles. Ejecuta NivelesSeeder primero.');
            return;
        }

        // 5️⃣ Crear area_nivel según la distribución
        $areaNivelData = [];
        foreach ($areas as $area) {
            if (in_array($area->id_area, [1, 2, 3])) { // Áreas 1,2,3 → 3 niveles
                for ($i = 1; $i <= 3; $i++) {
                    $areaNivelData[] = [
                        'id_area' => $area->id_area,
                        'id_nivel' => $i,
                        'id_grado_escolaridad' => $i,
                        'id_olimpiada' => $olimpiada->id_olimpiada,
                        'activo' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            } else { // Áreas 4,5 → 1 nivel (1ro)
                $areaNivelData[] = [
                    'id_area' => $area->id_area,
                    'id_nivel' => 1,
                    'id_grado_escolaridad' => 1,
                    'id_olimpiada' => $olimpiada->id_olimpiada,
                    'activo' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('area_nivel')->insert($areaNivelData);
        $this->command->info("✅ Registros en area_nivel creados correctamente.");

        // 6️⃣ Roles
        $rolResp = DB::table('rol')->where('nombre', 'Responsable Area')->first();
        $rolEval = DB::table('rol')->where('nombre', 'Evaluador')->first();
        if (!$rolResp || !$rolEval) {
            $this->command->error('❌ No se encontraron roles. Ejecuta RolesSeeder primero.');
            return;
        }

        // 7️⃣ Crear responsables
        $responsables = [
            ['nombre' => 'Resp1', 'apellido' => 'Sistema', 'areas' => [1, 2, 3]],
            ['nombre' => 'Resp2', 'apellido' => 'Sistema', 'areas' => [4]],
            ['nombre' => 'Resp3', 'apellido' => 'Sistema', 'areas' => [5]],
            ['nombre' => 'Resp4', 'apellido' => 'Sistema', 'areas' => [1, 2]],
            ['nombre' => 'Resp5', 'apellido' => 'Sistema', 'areas' => [3, 4]],
        ];

        $contadorEval = 1;

        foreach ($responsables as $resp) {
            $usuario = Usuario::create([
                'nombre' => $resp['nombre'],
                'apellido' => $resp['apellido'],
                'ci' => rand(1000000, 9999999),
                'email' => strtolower($resp['nombre'] . '@ohsansi.com'),
                'password' => Hash::make('responsable123'),
                'telefono' => '7' . rand(1000000, 9999999),
            ]);

            // Asignar rol de responsable
            DB::table('usuario_rol')->insert([
                'id_usuario' => $usuario->id_usuario,
                'id_rol' => $rolResp->id_rol,
                'id_olimpiada' => $olimpiada->id_olimpiada,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Asignar áreas al responsable
            foreach ($resp['areas'] as $id_area) {
                $areaOlimpiada = DB::table('area_olimpiada')
                    ->where('id_area', $id_area)
                    ->where('id_olimpiada', $olimpiada->id_olimpiada)
                    ->first();

                if ($areaOlimpiada) {
                    DB::table('responsable_area')->insert([
                        'id_usuario' => $usuario->id_usuario,
                        'id_area_olimpiada' => $areaOlimpiada->id_area_olimpiada,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            // 8️⃣ Crear evaluadores por área_nivel
            foreach ($resp['areas'] as $id_area) {
                $areaObj = $areas->where('id_area', $id_area)->first();
                $nombreArea = strtolower(str_replace(' ', '_', $areaObj->nombre));
                $nombreAreaEmail = iconv('UTF-8', 'ASCII//TRANSLIT', $nombreArea);
                $nombreAreaEmail = preg_replace('/[^A-Za-z0-9_]/', '', $nombreAreaEmail);

                $areaNiveles = DB::table('area_nivel')
                    ->where('id_area', $id_area)
                    ->where('id_olimpiada', $olimpiada->id_olimpiada)
                    ->get();

                foreach ($areaNiveles as $an) {
                    $cantidad = match ($id_area) {
                        1 => 1,
                        2 => 2,
                        3 => ($an->id_nivel == 1 ? 2 : 1),
                        4 => 2,
                        5 => 1,
                        default => 1,
                    };

                    for ($i = 0; $i < $cantidad; $i++) {
                        $eval = Usuario::create([
                            'nombre' => "Eval{$contadorEval}_{$nombreArea}_N{$an->id_nivel}",
                            'apellido' => 'Tester',
                            'ci' => rand(1000000, 9999999),
                            'email' => "eval_{$nombreAreaEmail}_n{$an->id_nivel}_{$contadorEval}@ohsansi.com",
                            'password' => Hash::make('evaluador123'),
                            'telefono' => '6' . rand(1000000, 9999999),
                        ]);

                        DB::table('usuario_rol')->insert([
                            'id_usuario' => $eval->id_usuario,
                            'id_rol' => $rolEval->id_rol,
                            'id_olimpiada' => $olimpiada->id_olimpiada,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);

                        DB::table('evaluador_an')->insert([
                            'id_usuario' => $eval->id_usuario,
                            'id_area_nivel' => $an->id_area_nivel,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);

                        $contadorEval++;
                    }
                }
            }
        }

        $this->command->info('🎯 Responsables y evaluadores creados correctamente.');
        $this->command->info('🔑 Contraseñas predeterminadas: responsable123 / evaluador123');
    }
}
