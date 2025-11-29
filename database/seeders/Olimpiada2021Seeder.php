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
use App\Model\Parametro;
use App\Model\ResponsableArea;
use App\Model\EvaluadorAn;
use App\Model\Competidor;
use App\Model\Inscripcion;
use App\Model\Evaluacion;
use App\Model\Grupo;
use App\Model\Competencia;
use App\Model\Medallero;
use App\Model\Departamento;
use Carbon\Carbon;

class Olimpiada2021Seeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('🚀 Iniciando seeder para la Olimpiada 2021 (V8)...');

            // 1. Crear la Olimpiada 2021
            $olimpiada = Olimpiada::firstOrCreate(
                ['gestion_olimp' => '2021'],
                ['nombre_olimp' => 'Olimpiada Científica Estudiantil 2021', 'estado_olimp' => false] // Ya pasó
            );
            $this->command->info("Olimpiada '{$olimpiada->nombre_olimp}' verificada.");

            // 2. Configurar Áreas y Niveles Básicos
            $areasNombres = ['Matemáticas', 'Física', 'Informática', 'Química', 'Biología'];
            $areas = [];
            foreach ($areasNombres as $nombre) {
                $areas[$nombre] = Area::firstOrCreate(['nombre_area' => $nombre]);
            }

            $nivelSecundaria = Nivel::firstOrCreate(['nombre_nivel' => 'Secundaria']);
            
            // Grados Escolares (Solo creamos algunos necesarios)
            $grados = [
                '1ro de Secundaria', '2do de Secundaria', '3ro de Secundaria', 
                '4to de Secundaria', '5to de Secundaria', '6to de Secundaria'
            ];
            
            $gradosObj = [];
            foreach ($grados as $nombreGrado) {
                $gradosObj[] = GradoEscolaridad::firstOrCreate(['nombre_grado' => $nombreGrado]);
            }

            // 3. Vincular Áreas a la Olimpiada 2021
            $areaOlimpiadas = [];
            foreach ($areas as $nombre => $area) {
                $areaOlimpiadas[$nombre] = AreaOlimpiada::firstOrCreate([
                    'id_area' => $area->id_area,
                    'id_olimpiada' => $olimpiada->id_olimpiada
                ]);
            }

            // 4. Crear Configuraciones Area-Nivel (Solo Secundaria para simplificar)
            $areaNiveles = [];
            foreach ($areaOlimpiadas as $nombre => $ao) {
                $areaNiveles[$nombre] = AreaNivel::firstOrCreate([
                    'id_area_olimpiada' => $ao->id_area_olimpiada,
                    'id_nivel' => $nivelSecundaria->id_nivel
                ], ['es_activo_area_nivel' => true]);
            }

            // 5. Crear Usuarios Responsables y Evaluadores
            $rolResponsable = Rol::where('nombre_rol', 'Responsable Area')->first();
            $rolEvaluador = Rol::where('nombre_rol', 'Evaluador')->first();

            // Responsable Matemáticas
            $persResp = Persona::create([
                'nombre_pers' => 'Zimme', 'apellido_pers' => 'Castro', 
                'ci_pers' => '6778891', 'email_pers' => 'zimme.castro@test.com', 
                'telefono_pers' => '78657123'
            ]);
            $userResp = Usuario::create([
                'id_persona' => $persResp->id_persona,
                'email_usuario' => 'zimme.castro@test.com',
                'password_usuario' => Hash::make('mundolibre')
            ]);
            $userResp->roles()->attach($rolResponsable->id_rol, ['id_olimpiada' => $olimpiada->id_olimpiada]);
            
            ResponsableArea::create([
                'id_usuario' => $userResp->id_usuario,
                'id_area_olimpiada' => $areaOlimpiadas['Matemáticas']->id_area_olimpiada
            ]);

            // Evaluador Matemáticas
            $persEval = Persona::create([
                'nombre_pers' => 'Sandra', 'apellido_pers' => 'Bullock', 
                'ci_pers' => '6546673', 'email_pers' => 'sandra.bullock@test.com', 
                'telefono_pers' => '78800727'
            ]);
            $userEval = Usuario::create([
                'id_persona' => $persEval->id_persona,
                'email_usuario' => 'sandra.bullock@test.com',
                'password_usuario' => Hash::make('password12')
            ]);
            $userEval->roles()->attach($rolEvaluador->id_rol, ['id_olimpiada' => $olimpiada->id_olimpiada]);
            
            $evaluadorAnMat = EvaluadorAn::create([
                'id_usuario' => $userEval->id_usuario,
                'id_area_nivel' => $areaNiveles['Matemáticas']->id_area_nivel,
                'estado_eva_an' => true
            ]);

            $this->command->info('Usuarios creados y asignados.');

            // 6. Crear Competidores e Inscripciones (Matemáticas)
            $institucion = Institucion::firstOrCreate(['nombre_inst' => 'Colegio 2021 Test']);
            $depto = Departamento::firstOrCreate(['nombre_dep' => 'La Paz']);
            
            $competidoresData = [
                ['nombre' => 'Ana', 'apellido' => 'Vacadiaz', 'ci' => '86985436', 'nota' => 95],
                ['nombre' => 'Juan', 'apellido' => 'Perez', 'ci' => '64567890', 'nota' => 88],
                ['nombre' => 'Sofia', 'apellido' => 'Villarroel', 'ci' => '15678901', 'nota' => 76],
                ['nombre' => 'Mateo', 'apellido' => 'Quispe', 'ci' => '56989012', 'nota' => 45],
            ];

            $inscripcionesCreadas = [];

            foreach ($competidoresData as $data) {
                // Persona
                $p = Persona::create([
                    'nombre_pers' => $data['nombre'], 'apellido_pers' => $data['apellido'],
                    'ci_pers' => $data['ci'], 'email_pers' => strtolower($data['nombre']).'@test2021.com',
                    'telefono_pers' => '0000000'
                ]);

                // Competidor (Asignamos un grado aleatorio de secundaria)
                $comp = Competidor::create([
                    'id_persona' => $p->id_persona,
                    'id_institucion' => $institucion->id_institucion,
                    'id_departamento' => $depto->id_departamento,
                    'id_grado_escolaridad' => $gradosObj[0]->id_grado_escolaridad, // 1ro Sec
                    'genero_competidor' => 'M',
                    'contacto_tutor_compe' => 'Tutor Test'
                ]);

                // Inscripción (En Matemáticas)
                $insc = Inscripcion::create([
                    'id_competidor' => $comp->id_competidor,
                    'id_area_nivel' => $areaNiveles['Matemáticas']->id_area_nivel
                ]);
                
                // Guardamos la nota temporalmente en el array para usarla después
                $inscripcionesCreadas[] = ['inscripcion' => $insc, 'nota' => $data['nota']];
            }

            // 7. Crear Competencia (Examen Final)
            $faseFinal = FaseGlobal::firstOrCreate(['codigo_fas_glo' => 'F3_NAC'], [
                'nombre_fas_glo' => 'Etapa Nacional', 'orden_fas_glo' => 3
            ]);

            $competencia = Competencia::create([
                'id_fase_global' => $faseFinal->id_fase_global,
                'id_area_nivel' => $areaNiveles['Matemáticas']->id_area_nivel,
                'nombre_examen' => 'Examen Final Matemáticas 2021',
                'fecha_inicio' => Carbon::create(2021, 10, 15, 9, 0, 0),
                'fecha_fin' => Carbon::create(2021, 10, 15, 12, 0, 0),
                'estado_comp' => false // Ya finalizó
            ]);

            // 8. Registrar Evaluaciones
            foreach ($inscripcionesCreadas as $item) {
                Evaluacion::create([
                    'id_inscripcion' => $item['inscripcion']->id_inscripcion,
                    'id_competencia' => $competencia->id_competencia,
                    'id_evaluador_an' => $evaluadorAnMat->id_evaluador_an,
                    'nota_evalu' => $item['nota'],
                    'estado_competidor_eva' => $item['nota'] > 51 ? 'APROBADO' : 'REPROBADO',
                    'fecha_evalu' => Carbon::create(2021, 10, 20),
                    'estado_evalu' => true
                ]);
            }

            // 9. Crear Medallero (Top 3)
            $medallas = ['ORO', 'PLATA', 'BRONCE'];
            for ($i = 0; $i < 3; $i++) {
                Medallero::create([
                    'id_inscripcion' => $inscripcionesCreadas[$i]['inscripcion']->id_inscripcion,
                    'id_competencia' => $competencia->id_competencia,
                    'puesto_medall' => $i + 1,
                    'medalla_medall' => $medallas[$i]
                ]);
            }

            // 10. Crear un Grupo de Finalistas
            $grupo = Grupo::create(['nombre_grupo' => 'Finalistas Matemáticas 2021']);
            // Asignar a los 3 ganadores
            for ($i = 0; $i < 3; $i++) {
                $grupo->inscripciones()->attach($inscripcionesCreadas[$i]['inscripcion']->id_inscripcion);
            }

            $this->command->info('¡Seeder de Olimpiada 2021 completado exitosamente!');
        });
    }
}