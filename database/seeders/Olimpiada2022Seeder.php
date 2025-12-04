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
use App\Model\ExamenConf;
use App\Model\Medallero;
use App\Model\Departamento;

class Olimpiada2022Seeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('⏳ Iniciando seeder histórico para Olimpiada 2022...');

            $gradosNombres = [
                '1ro de Secundaria', '2do de Secundaria', '3ro de Secundaria',
                '4to de Secundaria', '5to de Secundaria', '6to de Secundaria'
            ];
            foreach ($gradosNombres as $nombre) {
                GradoEscolaridad::firstOrCreate(['nombre' => $nombre]);
            }
            $grado1ro = GradoEscolaridad::where('nombre', '1ro de Secundaria')->first();
            $grado2do = GradoEscolaridad::where('nombre', '2do de Secundaria')->first();
            $olimpiada = Olimpiada::firstOrCreate(
                ['gestion' => '2022'],
                ['nombre' => 'Olimpiada Científica Estudiantil 2022', 'estado' => false]
            );

            $areasNombres = ['Matemáticas', 'Física', 'Informática', 'Química', 'Biología', 'Robótica'];
            foreach ($areasNombres as $nombre) {
                Area::firstOrCreate(['nombre' => $nombre]);
            }

            $areas = Area::whereIn('nombre', $areasNombres)->get();
            $nivel1 = Nivel::firstOrCreate(['nombre' => 'Nivel 1']);
            $nivel2 = Nivel::firstOrCreate(['nombre' => 'Nivel 2']);

            if ($areas->isEmpty()) {
                $this->command->error('❌ Error crítico: No se pudieron crear las áreas.');
                return;
            }

            $mapaAreaOlimpiada = [];
            foreach ($areas as $area) {
                $ao = AreaOlimpiada::firstOrCreate([
                    'id_area' => $area->id_area,
                    'id_olimpiada' => $olimpiada->id_olimpiada
                ]);
                $mapaAreaOlimpiada[$area->nombre] = $ao;
            }

            $pResp = Persona::firstOrCreate(['ci' => 'RESP-2022'], [
                'nombre' => 'Roberto', 'apellido' => 'Carlos', 'email' => 'roberto.2022@test.com', 'telefono' => '70002022'
            ]);
            $uResp = Usuario::firstOrCreate(['email' => 'roberto.2022@test.com'], [
                'id_persona' => $pResp->id_persona,
                'password' => 'admin2022'
            ]);

            $pEval = Persona::firstOrCreate(['ci' => 'EVAL-2022'], [
                'nombre' => 'Julia', 'apellido' => 'Roberts', 'email' => 'julia.2022@test.com', 'telefono' => '70002023'
            ]);
            $uEval = Usuario::firstOrCreate(['email' => 'julia.2022@test.com'], [
                'id_persona' => $pEval->id_persona,
                'password' => 'eval2022'
            ]);

            $rolResp = Rol::firstOrCreate(['nombre' => 'Responsable Area']);
            $rolEval = Rol::firstOrCreate(['nombre' => 'Evaluador']);

            $uResp->roles()->syncWithoutDetaching([$rolResp->id_rol => ['id_olimpiada' => $olimpiada->id_olimpiada]]);
            $uEval->roles()->syncWithoutDetaching([$rolEval->id_rol => ['id_olimpiada' => $olimpiada->id_olimpiada]]);

            if (isset($mapaAreaOlimpiada['Robótica'])) {
                ResponsableArea::firstOrCreate(['id_usuario' => $uResp->id_usuario, 'id_area_olimpiada' => $mapaAreaOlimpiada['Robótica']->id_area_olimpiada]);
            }
            if (isset($mapaAreaOlimpiada['Física'])) {
                ResponsableArea::firstOrCreate(['id_usuario' => $uResp->id_usuario, 'id_area_olimpiada' => $mapaAreaOlimpiada['Física']->id_area_olimpiada]);
            }

            $anRobotica = null;
            if (isset($mapaAreaOlimpiada['Robótica'])) {
                $anRobotica = AreaNivel::firstOrCreate([
                    'id_area_olimpiada' => $mapaAreaOlimpiada['Robótica']->id_area_olimpiada,
                    'id_nivel' => $nivel1->id_nivel
                ], ['es_activo' => true]);

                $anRobotica->gradosEscolaridad()->syncWithoutDetaching([
                    $grado1ro->id_grado_escolaridad,
                    $grado2do->id_grado_escolaridad
                ]);
            }

            $evaluadorAn = null;
            if ($anRobotica) {
                $evaluadorAn = EvaluadorAn::firstOrCreate([
                    'id_usuario' => $uEval->id_usuario,
                    'id_area_nivel' => $anRobotica->id_area_nivel,
                    'estado' => true
                ]);
            }

            $inst = Institucion::firstOrCreate(['nombre' => 'Instituto Americano']);
            $depto = Departamento::firstOrCreate(['nombre' => 'Cochabamba']);

            $competidoresData = [
                ['nombre' => 'Alan', 'apellido' => 'Turing', 'ci' => 'ROB-001', 'genero' => 'M'],
                ['nombre' => 'Ada', 'apellido' => 'Lovelace', 'ci' => 'ROB-002', 'genero' => 'F'],
                ['nombre' => 'Nikola', 'apellido' => 'Tesla', 'ci' => 'ROB-003', 'genero' => 'M'],
            ];

            $competidoresCreados = [];
            foreach ($competidoresData as $data) {
                $p = Persona::firstOrCreate(['ci' => $data['ci']], [
                    'nombre' => $data['nombre'],
                    'apellido' => $data['apellido'],
                    'email' => strtolower($data['nombre']).'.rob@test.com',
                    'telefono' => '0000000'
                ]);

                if ($anRobotica) {
                    $competidoresCreados[] = Competidor::firstOrCreate([
                        'id_persona' => $p->id_persona,
                        'id_area_nivel' => $anRobotica->id_area_nivel
                    ], [
                        'id_institucion' => $inst->id_institucion,
                        'id_departamento' => $depto->id_departamento,
                        'id_grado_escolaridad' => $grado1ro->id_grado_escolaridad,
                        'contacto_tutor' => 'TutorRob22',
                        'genero' => $data['genero'],
                        'estado_evaluacion' => 'finalizado'
                    ]);
                }
            }

            $this->command->info('Competidores de Robótica 2022 creados.');

            $faseFinal = FaseGlobal::firstOrCreate(
                ['codigo' => 'FIN-22', 'id_olimpiada' => $olimpiada->id_olimpiada],
                ['nombre' => 'Fase Final Nacional', 'orden' => 3]
            );

            $competencia = null;
            $examen = null;
            if ($anRobotica) {
                $competencia = Competencia::create([
                    'fecha_inicio' => '2022-10-20',
                    'fecha_fin' => '2022-10-21',
                    'estado' => false,
                    'id_fase_global' => $faseFinal->id_fase_global,
                    'id_area_nivel' => $anRobotica->id_area_nivel,
                ]);

                $examen = ExamenConf::create([
                    'id_competencia' => $competencia->id_competencia,
                    'nombre' => 'Presentación Proyectos Robótica 2022',
                    'ponderacion' => 100,
                    'maxima_nota' => 100,
                ]);
            }

            if ($competencia && $examen && $evaluadorAn) {
                $notas = [98.50, 99.00, 85.00];
                foreach ($competidoresCreados as $idx => $comp) {
                    Evaluacion::create([
                        'id_competidor' => $comp->id_competidor,
                        'id_examen_conf' => $examen->id_examen_conf,
                        'id_evaluador_an' => $evaluadorAn->id_evaluador_an,
                        'nota' => $notas[$idx] ?? 0,
                        'fecha' => '2022-10-20 14:00:00',
                        'estado' => true,
                        'estado_competidor' => 'finalizado',
                        'observacion' => 'Excelente prototipo.'
                    ]);
                }

                Medallero::create(['puesto' => 1, 'medalla' => 'Oro', 'id_competidor' => $competidoresCreados[1]->id_competidor, 'id_competencia' => $competencia->id_competencia]);
                Medallero::create(['puesto' => 2, 'medalla' => 'Plata', 'id_competidor' => $competidoresCreados[0]->id_competidor, 'id_competencia' => $competencia->id_competencia]);
                Medallero::create(['puesto' => 3, 'medalla' => 'Bronce', 'id_competidor' => $competidoresCreados[2]->id_competidor, 'id_competencia' => $competencia->id_competencia]);
            }

            $this->command->info('✅ Seeder Olimpiada 2022 (Robótica) completado.');
        });
    }
}
