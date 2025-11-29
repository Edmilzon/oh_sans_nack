<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Model\Area;
use App\Model\Nivel;
use App\Model\Olimpiada;
use App\Model\Usuario;
use App\Model\Persona;
use App\Model\GradoEscolaridad;
use App\Model\Rol;

class AreasEvaluadoresSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // 1️⃣ Verificar que existan grados escolares
        // Nota: En V8 ya no usamos GradoEscolaridad directamente en area_nivel, 
        // sino a través de nivel_grado, pero validamos su existencia.
        $grados = GradoEscolaridad::all();
        if ($grados->isEmpty()) {
            $this->command->error('❌ No hay grados de escolaridad. Ejecuta primero: php artisan db:seed --class=GradoEscolaridadSeeder');
            return;
        }

        // 2️⃣ Olimpiada del año actual
        $olimpiada = Olimpiada::where('gestion_olimp', date('Y'))->first();
        if (!$olimpiada) {
            // Fallback: toma la primera si no hay del año actual (para pruebas)
            $olimpiada = Olimpiada::first();
            if (!$olimpiada) {
                $this->command->error('❌ No se encontró ninguna olimpiada.');
                return;
            }
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
        $this->command->info('🏗️ Creando configuraciones Area-Nivel...');
        
        $areaNivelData = [];
        foreach ($areas as $area) {
            // Primero necesitamos el ID de area_olimpiada
            $areaOlimpiada = DB::table('area_olimpiada')
                ->where('id_area', $area->id_area)
                ->where('id_olimpiada', $olimpiada->id_olimpiada)
                ->first();

            if (!$areaOlimpiada) {
                // Si no existe la relación, la creamos al vuelo
                $idAreaOlimp = DB::table('area_olimpiada')->insertGetId([
                    'id_area' => $area->id_area,
                    'id_olimpiada' => $olimpiada->id_olimpiada,
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
            } else {
                $idAreaOlimp = $areaOlimpiada->id_area_olimpiada;
            }

            // Lógica de niveles por área
            if (in_array($area->id_area, [1, 2, 3])) { // Áreas principales (ej: Mate, Física) -> Tienen 3 niveles
                $nivelesAsignar = $niveles->take(3); // Niveles 1, 2, 3
            } else { // Áreas secundarias -> Solo nivel 1
                $nivelesAsignar = $niveles->take(1);
            }

            foreach ($nivelesAsignar as $nivel) {
                // Evitar duplicados
                $existe = DB::table('area_nivel')
                    ->where('id_area_olimpiada', $idAreaOlimp)
                    ->where('id_nivel', $nivel->id_nivel)
                    ->exists();

                if (!$existe) {
                    $areaNivelData[] = [
                        'id_area_olimpiada' => $idAreaOlimp,
                        'id_nivel' => $nivel->id_nivel,
                        'es_activo_area_nivel' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        if (!empty($areaNivelData)) {
            DB::table('area_nivel')->insert($areaNivelData);
        }
        $this->command->info("✅ Registros en area_nivel verificados/creados.");

        // 6️⃣ Roles
        $rolResp = Rol::where('nombre_rol', 'Responsable Area')->first();
        $rolEval = Rol::where('nombre_rol', 'Evaluador')->first();
        
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
            // A. Crear Persona
            $persona = Persona::create([
                'nombre_pers' => $resp['nombre'],
                'apellido_pers' => $resp['apellido'],
                'ci_pers' => rand(1000000, 9999999),
                'telefono_pers' => '7' . rand(1000000, 9999999),
                'email_pers' => strtolower($resp['nombre'] . '@ohsansi.com'),
            ]);

            // B. Crear Usuario
            $usuario = Usuario::create([
                'id_persona' => $persona->id_persona,
                'email_usuario' => $persona->email_pers,
                'password_usuario' => Hash::make('responsable123'),
            ]);

            // C. Asignar Rol
            $usuario->roles()->attach($rolResp->id_rol, ['id_olimpiada' => $olimpiada->id_olimpiada]);

            // D. Asignar áreas al responsable
            foreach ($resp['areas'] as $id_area) {
                $areaOlimpiada = DB::table('area_olimpiada')
                    ->where('id_area', $id_area)
                    ->where('id_olimpiada', $olimpiada->id_olimpiada)
                    ->first();

                if ($areaOlimpiada) {
                    DB::table('responsable_area')->insertOrIgnore([
                        'id_usuario' => $usuario->id_usuario,
                        'id_area_olimpiada' => $areaOlimpiada->id_area_olimpiada,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            // 8️⃣ Crear evaluadores por área_nivel (Anidados al responsable para simular su equipo)
            foreach ($resp['areas'] as $id_area) {
                $areaObj = $areas->where('id_area', $id_area)->first();
                
                // Obtener configuración de niveles para esta área
                $idAreaOlimp = DB::table('area_olimpiada')
                    ->where('id_area', $id_area)
                    ->where('id_olimpiada', $olimpiada->id_olimpiada)
                    ->value('id_area_olimpiada');

                $areaNiveles = DB::table('area_nivel')
                    ->where('id_area_olimpiada', $idAreaOlimp)
                    ->get();

                if (!$areaObj) continue;

                $nombreArea = strtolower(str_replace(' ', '_', $areaObj->nombre_area));
                // Limpiar caracteres especiales para el email
                $nombreAreaEmail = preg_replace('/[^A-Za-z0-9_]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $nombreArea));

                foreach ($areaNiveles as $an) {
                    // Lógica de cantidad de evaluadores por nivel
                    $cantidad = match ($id_area) {
                        1 => 1,
                        2 => 2,
                        3 => ($an->id_nivel == 1 ? 2 : 1),
                        4 => 2,
                        5 => 1,
                        default => 1,
                    };

                    for ($i = 0; $i < $cantidad; $i++) {
                        // Crear Persona Evaluador
                        $personaEval = Persona::create([
                            'nombre_pers' => "Eval{$contadorEval}_{$nombreArea}_N{$an->id_nivel}",
                            'apellido_pers' => 'Tester',
                            'ci_pers' => rand(1000000, 9999999),
                            'telefono_pers' => '6' . rand(1000000, 9999999),
                            'email_pers' => "eval_{$nombreAreaEmail}_n{$an->id_nivel}_{$contadorEval}@ohsansi.com",
                        ]);

                        // Crear Usuario Evaluador
                        $eval = Usuario::create([
                            'id_persona' => $personaEval->id_persona,
                            'email_usuario' => $personaEval->email_pers,
                            'password_usuario' => Hash::make('evaluador123'),
                        ]);

                        // Asignar Rol Evaluador
                        $eval->roles()->attach($rolEval->id_rol, ['id_olimpiada' => $olimpiada->id_olimpiada]);

                        // Asignar Permiso de Evaluación (Tabla evaluador_an)
                        DB::table('evaluador_an')->insert([
                            'id_usuario' => $eval->id_usuario,
                            'id_area_nivel' => $an->id_area_nivel,
                            'estado_eva_an' => true,
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