<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
use App\Model\ResponsableArea;
use App\Model\EvaluadorAn;
use App\Model\Competidor;
use App\Model\Inscripcion;
use App\Model\Evaluacion;
use App\Model\Competencia;
use App\Model\Medallero;
use App\Model\Departamento;
use Carbon\Carbon;

class Olimpiada2022Seeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('🚀 Iniciando seeder para la Olimpiada 2022 (V8)...');

            // 1. Crear la Olimpiada 2022 (Estado inactivo porque ya pasó)
            $olimpiada = Olimpiada::firstOrCreate(
                ['gestion_olimp' => '2022'],
                ['nombre_olimp' => 'Olimpiada Científica Estudiantil 2022', 'estado_olimp' => false]
            );

            // 2. Configurar Áreas y Niveles
            // Asumimos que las áreas base ya existen por AreasSeeder
            $areasNombres = ['Matemáticas', 'Física', 'Química', 'Robótica'];
            $areas = Area::whereIn('nombre_area', $areasNombres)->get();

            $nivelSecundaria = Nivel::firstOrCreate(['nombre_nivel' => 'Secundaria']);
            
            // Grado escolar específico para este ejemplo
            $grado4to = GradoEscolaridad::firstOrCreate(['nombre_grado' => '4to de Secundaria']);

            // 3. Vincular Áreas a la Olimpiada 2022
            $areaOlimpiadas = [];
            foreach ($areas as $area) {
                $areaOlimpiadas[$area->nombre_area] = AreaOlimpiada::firstOrCreate([
                    'id_area' => $area->id_area,
                    'id_olimpiada' => $olimpiada->id_olimpiada
                ]);
            }

            // 4. Crear Configuraciones Area-Nivel (Solo Secundaria)
            $areaNiveles = [];
            foreach ($areaOlimpiadas as $nombre => $ao) {
                $areaNiveles[$nombre] = AreaNivel::firstOrCreate([
                    'id_area_olimpiada' => $ao->id_area_olimpiada,
                    'id_nivel' => $nivelSecundaria->id_nivel
                ], ['es_activo_area_nivel' => true]);
            }

            // 5. Crear Personal (Responsable y Evaluador de Robótica 2022)
            $rolResponsable = Rol::where('nombre_rol', 'Responsable Area')->first();
            $rolEvaluador = Rol::where('nombre_rol', 'Evaluador')->first();

            // Responsable Robótica
            $persResp = Persona::create([
                'nombre_pers' => 'Roberto', 'apellido_pers' => 'Tica', 
                'ci_pers' => '9988776', 'email_pers' => 'roberto.robotica@2022.com', 
                'telefono_pers' => '70020221'
            ]);
            $userResp = Usuario::create([
                'id_persona' => $persResp->id_persona,
                'email_usuario' => 'roberto.robotica@2022.com',
                'password_usuario' => Hash::make('robot2022')
            ]);
            $userResp->roles()->attach($rolResponsable->id_rol, ['id_olimpiada' => $olimpiada->id_olimpiada]);
            
            if (isset($areaOlimpiadas['Robótica'])) {
                ResponsableArea::create([
                    'id_usuario' => $userResp->id_usuario,
                    'id_area_olimpiada' => $areaOlimpiadas['Robótica']->id_area_olimpiada
                ]);
            }

            // Evaluador Robótica
            $persEval = Persona::create([
                'nombre_pers' => 'Eva', 'apellido_pers' => 'Luadora', 
                'ci_pers' => '5544332', 'email_pers' => 'eva.2022@test.com', 
                'telefono_pers' => '70020222'
            ]);
            $userEval = Usuario::create([
                'id_persona' => $persEval->id_persona,
                'email_usuario' => 'eva.2022@test.com',
                'password_usuario' => Hash::make('eval2022')
            ]);
            $userEval->roles()->attach($rolEvaluador->id_rol, ['id_olimpiada' => $olimpiada->id_olimpiada]);
            
            if (isset($areaNiveles['Robótica'])) {
                $evaluadorAnRobotica = EvaluadorAn::create([
                    'id_usuario' => $userEval->id_usuario,
                    'id_area_nivel' => $areaNiveles['Robótica']->id_area_nivel,
                    'estado_eva_an' => true
                ]);
            }

            // 6. Crear Competidores e Inscripciones (Robótica)
            $institucion = Institucion::firstOrCreate(['nombre_inst' => 'Instituto Tecnológico 2022']);
            $depto = Departamento::firstOrCreate(['nombre_dep' => 'Santa Cruz']);
            
            $competidoresData = [
                ['nombre' => 'Alan', 'apellido' => 'Turing', 'ci' => '1010101', 'nota' => 98.5],
                ['nombre' => 'Ada', 'apellido' => 'Lovelace', 'ci' => '0101010', 'nota' => 99.0],
                ['nombre' => 'Elon', 'apellido' => 'Musk', 'ci' => '3332221', 'nota' => 60.0],
                ['nombre' => 'Nikola', 'apellido' => 'Tesla', 'ci' => '9999999', 'nota' => 85.0],
            ];

            $inscripcionesCreadas = [];

            if (isset($areaNiveles['Robótica'])) {
                foreach ($competidoresData as $data) {
                    // Persona
                    $p = Persona::create([
                        'nombre_pers' => $data['nombre'], 'apellido_pers' => $data['apellido'],
                        'ci_pers' => $data['ci'], 'email_pers' => strtolower($data['nombre']).'@robo2022.com',
                        'telefono_pers' => '0000000'
                    ]);

                    // Competidor
                    $comp = Competidor::create([
                        'id_persona' => $p->id_persona,
                        'id_institucion' => $institucion->id_institucion,
                        'id_departamento' => $depto->id_departamento,
                        'id_grado_escolaridad' => $grado4to->id_grado_escolaridad,
                        'genero_competidor' => 'M', // Simplificado
                        'contacto_tutor_compe' => 'Tutor 2022'
                    ]);

                    // Inscripción
                    $insc = Inscripcion::create([
                        'id_competidor' => $comp->id_competidor,
                        'id_area_nivel' => $areaNiveles['Robótica']->id_area_nivel
                    ]);
                    
                    $inscripcionesCreadas[] = ['inscripcion' => $insc, 'nota' => $data['nota']];
                }

                // 7. Crear Competencia (Examen Departamental)
                $faseDep = FaseGlobal::firstOrCreate(['codigo_fas_glo' => 'F2_DEP'], [
                    'nombre_fas_glo' => 'Etapa Departamental', 'orden_fas_glo' => 3
                ]);

                $competencia = Competencia::create([
                    'id_fase_global' => $faseDep->id_fase_global,
                    'id_area_nivel' => $areaNiveles['Robótica']->id_area_nivel,
                    'nombre_examen' => 'Competencia Robótica SCZ 2022',
                    'fecha_inicio' => Carbon::create(2022, 9, 10, 8, 0, 0),
                    'fecha_fin' => Carbon::create(2022, 9, 10, 16, 0, 0),
                    'estado_comp' => false
                ]);

                // 8. Registrar Evaluaciones
                foreach ($inscripcionesCreadas as $item) {
                    Evaluacion::create([
                        'id_inscripcion' => $item['inscripcion']->id_inscripcion,
                        'id_competencia' => $competencia->id_competencia,
                        'id_evaluador_an' => $evaluadorAnRobotica->id_evaluador_an,
                        'nota_evalu' => $item['nota'],
                        'estado_competidor_eva' => $item['nota'] > 51 ? 'CLASIFICADO' : 'REPROBADO',
                        'fecha_evalu' => Carbon::create(2022, 9, 12),
                        'estado_evalu' => true
                    ]);
                }

                // 9. Medallero (Top 2)
                // Ada (99) y Alan (98.5)
                Medallero::create([
                    'id_inscripcion' => $inscripcionesCreadas[1]['inscripcion']->id_inscripcion, // Ada
                    'id_competencia' => $competencia->id_competencia,
                    'puesto_medall' => 1,
                    'medalla_medall' => 'ORO'
                ]);
                Medallero::create([
                    'id_inscripcion' => $inscripcionesCreadas[0]['inscripcion']->id_inscripcion, // Alan
                    'id_competencia' => $competencia->id_competencia,
                    'puesto_medall' => 2,
                    'medalla_medall' => 'PLATA'
                ]);
            }

            $this->command->info('✅ Seeder de Olimpiada 2022 completado.');
        });
    }
}