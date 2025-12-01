<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Model\Area;
use App\Model\Nivel;
use App\Model\Olimpiada;
use App\Model\Usuario;
use App\Model\Persona;
use App\Model\Rol;
use App\Model\GradoEscolaridad;
use App\Model\AreaOlimpiada;
use App\Model\AreaNivel;
use App\Model\ResponsableArea;
use App\Model\EvaluadorAn;

class AreasEvaluadoresSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $now = now();

            // 1️⃣ Validaciones previas
            $grados = GradoEscolaridad::all();
            $areas = Area::all();
            $niveles = Nivel::all();

            // Buscar olimpiada actual o la última creada
            $olimpiada = Olimpiada::where('gestion', date('Y'))->first()
                         ?? Olimpiada::latest('id_olimpiada')->first();

            if ($grados->isEmpty() || $areas->isEmpty() || $niveles->isEmpty() || !$olimpiada) {
                $this->command->error('❌ Faltan datos base (Grados, Áreas, Niveles u Olimpiada). Ejecuta los seeders anteriores.');
                return;
            }

            $this->command->info("Procesando para Olimpiada: {$olimpiada->nombre}");

            // 2️⃣ Obtener Roles
            $rolResp = Rol::where('nombre', 'Responsable Area')->first();
            $rolEval = Rol::where('nombre', 'Evaluador')->first();

            if (!$rolResp || !$rolEval) {
                $this->command->error('❌ Roles no encontrados.');
                return;
            }

            // 3️⃣ Crear Estructura Académica (AreaOlimpiada -> AreaNivel -> Grados)
            $this->command->info('Configurando niveles por área...');

            foreach ($areas as $area) {
                // A. Crear AreaOlimpiada
                $areaOlimpiada = AreaOlimpiada::firstOrCreate([
                    'id_area' => $area->id_area,
                    'id_olimpiada' => $olimpiada->id_olimpiada
                ]);

                // Lógica de niveles: Áreas 1,2,3 tienen 3 niveles. Las demás solo 1.
                // (Asumiendo que IDs 1,2,3 son las ciencias duras como Mat, Fis, Quim)
                $maxNiveles = in_array($area->id_area, [1, 2, 3]) ? 3 : 1;

                for ($i = 1; $i <= $maxNiveles; $i++) {
                    // Buscar el objeto nivel (1ro, 2do, etc)
                    // Asumimos que el Nivel con ID $i corresponde al iterador (cuidado si borraste niveles)
                    $nivelObj = $niveles->skip($i - 1)->first();
                    $gradoObj = $grados->skip($i - 1)->first(); // Asumimos correspondencia 1 a 1 para este ejemplo

                    if ($nivelObj && $gradoObj) {
                        // B. Crear AreaNivel
                        $areaNivel = AreaNivel::firstOrCreate([
                            'id_area_olimpiada' => $areaOlimpiada->id_area_olimpiada,
                            'id_nivel' => $nivelObj->id_nivel
                        ], [
                            'es_activo' => true
                        ]);

                        // C. Asociar Grado (Tabla pivote area_nivel_grado)
                        $areaNivel->gradosEscolaridad()->syncWithoutDetaching([$gradoObj->id_grado_escolaridad]);
                    }
                }
            }

            // 4️⃣ Crear Responsables de Área
            $responsablesData = [
                ['nombre' => 'Resp1', 'apellido' => 'Sistema', 'areas_ids' => [1, 2, 3]],
                ['nombre' => 'Resp2', 'apellido' => 'Sistema', 'areas_ids' => [4]], // Biología?
                ['nombre' => 'Resp3', 'apellido' => 'Sistema', 'areas_ids' => [5]], // Informática?
                ['nombre' => 'Resp4', 'apellido' => 'Sistema', 'areas_ids' => [1, 2]], // Multidisciplinario
                ['nombre' => 'Resp5', 'apellido' => 'Sistema', 'areas_ids' => [3, 4]],
            ];

            foreach ($responsablesData as $data) {
                // Crear Persona
                $persona = Persona::firstOrCreate(['ci' => 'R-' . rand(1000, 9999) . $data['nombre']], [
                    'nombre' => $data['nombre'],
                    'apellido' => $data['apellido'],
                    'email' => strtolower($data['nombre']) . '@personal.com',
                    'telefono' => '7000' . rand(1000, 9999)
                ]);

                // Crear Usuario
                $usuario = Usuario::firstOrCreate(['email' => strtolower($data['nombre']) . '@ohsansi.com'], [
                    'id_persona' => $persona->id_persona,
                    'password' => 'responsable123'
                ]);

                // Asignar Rol
                if (!$usuario->roles()->where('rol.id_rol', $rolResp->id_rol)->exists()) {
                    $usuario->roles()->attach($rolResp->id_rol, ['id_olimpiada' => $olimpiada->id_olimpiada]);
                }

                // Asignar Áreas de Responsabilidad
                foreach ($data['areas_ids'] as $areaId) {
                    $ao = AreaOlimpiada::where('id_area', $areaId)
                        ->where('id_olimpiada', $olimpiada->id_olimpiada)
                        ->first();

                    if ($ao) {
                        ResponsableArea::firstOrCreate([
                            'id_usuario' => $usuario->id_usuario,
                            'id_area_olimpiada' => $ao->id_area_olimpiada
                        ]);
                    }
                }
            }
            $this->command->info("✅ Responsables creados.");

            // 5️⃣ Crear Evaluadores (1 o 2 por cada Area-Nivel)
            $contadorEval = 1;

            // Recorremos todas las configuraciones creadas en esta olimpiada
            $todosAreaNiveles = AreaNivel::with(['areaOlimpiada.area', 'nivel'])
                ->whereHas('areaOlimpiada', function($q) use ($olimpiada) {
                    $q->where('id_olimpiada', $olimpiada->id_olimpiada);
                })->get();

            foreach ($todosAreaNiveles as $an) {
                $areaId = $an->areaOlimpiada->id_area;

                // Lógica de cantidad definida por ti:
                // Area 1, 3, 5 = 1 evaluador. Area 2, 4 = 2 evaluadores.
                // Excepción: Area 3 Nivel 1 = 2 evaluadores.
                $cantidad = 1;
                if (in_array($areaId, [2, 4])) $cantidad = 2;
                if ($areaId == 3 && $an->nivel->nombre == '1ro de Secundaria') $cantidad = 2;

                for ($i = 0; $i < $cantidad; $i++) {
                    $nombreArea = preg_replace('/[^A-Za-z0-9]/', '', $an->areaOlimpiada->area->nombre);

                    // Persona
                    $pEval = Persona::create([
                        'nombre' => "Eval{$contadorEval}",
                        'apellido' => "Area{$areaId}",
                        'ci' => rand(100000, 999999) . "-E",
                        'email' => "p.eval{$contadorEval}@test.com",
                        'telefono' => '6000' . rand(1000, 9999)
                    ]);

                    // Usuario
                    $uEval = Usuario::create([
                        'id_persona' => $pEval->id_persona,
                        'email' => "eval.{$nombreArea}.{$contadorEval}@ohsansi.com",
                        'password' => 'evaluador123'
                    ]);

                    // Rol
                    $uEval->roles()->attach($rolEval->id_rol, ['id_olimpiada' => $olimpiada->id_olimpiada]);

                    // Asignación específica (EvaluadorAn)
                    EvaluadorAn::create([
                        'id_usuario' => $uEval->id_usuario,
                        'id_area_nivel' => $an->id_area_nivel,
                        'estado' => true
                    ]);

                    $contadorEval++;
                }
            }

            $this->command->info("✅ Evaluadores creados y asignados a sus niveles.");
        });
    }
}
