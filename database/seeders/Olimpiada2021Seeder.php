<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Model\Olimpiada;
use App\Model\Area;
use App\Model\Nivel;
use App\Model\GradoEscolaridad;
use App\Model\Persona;
use App\Model\Usuario;
use App\Model\Rol;
use App\Model\Institucion;
use App\Model\AreaOlimpiada;
use App\Model\AreaNivel;
use App\Model\FaseGlobal;
use App\Model\Parametro;
use App\Model\ResponsableArea;
use App\Model\EvaluadorAn;
use App\Model\Competidor;
use App\Model\Evaluacion;
use App\Model\Grupo;
use App\Model\Competencia;
use App\Model\Medallero;
use App\Model\Departamento;

class Olimpiada2021Seeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('🕰️ Iniciando seeder histórico para Olimpiada 2021...');

            // 1. Grados
            $grados = ['1ro de Secundaria', '2do de Secundaria', '3ro de Secundaria', '4to de Secundaria', '5to de Secundaria', '6to de Secundaria'];
            foreach ($grados as $nombre) GradoEscolaridad::firstOrCreate(['nombre' => $nombre]);
            $grado1Sec = GradoEscolaridad::where('nombre', '1ro de Secundaria')->first();

            // 2. Olimpiada
            $olimpiada = Olimpiada::firstOrCreate(['gestion' => '2021'], ['nombre' => 'Olimpiada 2021', 'estado' => false]);

            // 3. Areas y Niveles
            $areas = Area::all();
            $nivel1 = Nivel::firstOrCreate(['nombre' => 'Nivel 1']);
            if ($areas->isEmpty()) return;

            // 4. AreaOlimpiada
            $mapaAreaOlimpiada = [];
            foreach ($areas as $area) {
                $ao = AreaOlimpiada::firstOrCreate(['id_area' => $area->id_area, 'id_olimpiada' => $olimpiada->id_olimpiada]);
                $mapaAreaOlimpiada[$area->nombre] = $ao;
            }

            // 5. Usuarios (Sin Genero en Persona)
            $pResp = Persona::firstOrCreate(['ci' => '6778891'], ['nombre' => 'Zimme', 'apellido' => 'Castro', 'email' => 'zimme@test.com', 'telefono' => '78657123']);
            $uResp = Usuario::firstOrCreate(['email' => 'zimme@test.com'], ['id_persona' => $pResp->id_persona, 'password' => 'mundolibre']);

            $pEval = Persona::firstOrCreate(['ci' => '6546673'], ['nombre' => 'Sandra', 'apellido' => 'Bullock', 'email' => 'sandra@test.com', 'telefono' => '7800727']);
            $uEval = Usuario::firstOrCreate(['email' => 'sandra@test.com'], ['id_persona' => $pEval->id_persona, 'password' => 'password12']);

            // Roles
            $rolResp = Rol::where('nombre', 'Responsable Area')->first();
            $rolEval = Rol::where('nombre', 'Evaluador')->first();
            if ($rolResp) $uResp->roles()->syncWithoutDetaching([$rolResp->id_rol => ['id_olimpiada' => $olimpiada->id_olimpiada]]);
            if ($rolEval) $uEval->roles()->syncWithoutDetaching([$rolEval->id_rol => ['id_olimpiada' => $olimpiada->id_olimpiada]]);

            // 6. AreaNivel (Matemáticas)
            if (isset($mapaAreaOlimpiada['Matemáticas'])) {
                $anMat = AreaNivel::firstOrCreate(['id_area_olimpiada' => $mapaAreaOlimpiada['Matemáticas']->id_area_olimpiada, 'id_nivel' => $nivel1->id_nivel], ['es_activo' => true]);
                $anMat->gradosEscolaridad()->syncWithoutDetaching([$grado1Sec->id_grado_escolaridad]);

                // 7. EvaluadorAn
                $evaluadorAn = EvaluadorAn::firstOrCreate(['id_usuario' => $uEval->id_usuario, 'id_area_nivel' => $anMat->id_area_nivel, 'estado' => true]);

                // 8. Competidores
                $inst = Institucion::firstOrCreate(['nombre' => 'Colegio Don Bosco']);
                $depto = Departamento::firstOrCreate(['nombre' => 'La Paz']);

                $dataEstudiantes = [
                    ['nom' => 'Ana', 'ape' => 'Vaca', 'ci' => '86985436', 'gen' => 'F'],
                    ['nom' => 'Juan', 'ape' => 'Chavez', 'ci' => '64567890', 'gen' => 'M']
                ];

                foreach ($dataEstudiantes as $est) {
                    $p = Persona::firstOrCreate(['ci' => $est['ci']], [
                        'nombre' => $est['nom'], 'apellido' => $est['ape'], 'email' => $est['nom'].'@test.com', 'telefono' => '0000000'
                        // SIN GENERO AQUI
                    ]);

                    Competidor::firstOrCreate(['id_persona' => $p->id_persona, 'id_area_nivel' => $anMat->id_area_nivel], [
                        'id_institucion' => $inst->id_institucion,
                        'id_departamento' => $depto->id_departamento,
                        'id_grado_escolaridad' => $grado1Sec->id_grado_escolaridad,
                        'contacto_tutor' => 'Tutor21',
                        'genero' => $est['gen'], // CON GENERO AQUI
                        'estado_evaluacion' => 'finalizado'
                    ]);
                }
            }
            $this->command->info('✅ Seeder Olimpiada 2021 completado.');
        });
    }
}
