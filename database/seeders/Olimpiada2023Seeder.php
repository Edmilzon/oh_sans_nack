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

class Olimpiada2023Seeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('🚀 Iniciando seeder para la Olimpiada 2023 (V8)...');

            // 1. Crear la Olimpiada 2023
            $olimpiada = Olimpiada::firstOrCreate(
                ['gestion_olimp' => '2023'],
                ['nombre_olimp' => 'Olimpiada Científica Estudiantil 2023', 'estado_olimp' => false]
            );

            // 2. Configurar Áreas y Niveles (Solo las relevantes para 2023)
            $areasNombres = ['Matemáticas', 'Física', 'Informática'];
            $areas = Area::whereIn('nombre_area', $areasNombres)->get();

            $nivelSecundaria = Nivel::firstOrCreate(['nombre_nivel' => 'Secundaria']);
            
            // Grados
            $grado1ro = GradoEscolaridad::firstOrCreate(['nombre_grado' => '1ro de Secundaria']);
            $grado2do = GradoEscolaridad::firstOrCreate(['nombre_grado' => '2do de Secundaria']);

            // 3. Vincular Áreas a la Olimpiada 2023
            $areaOlimpiadas = [];
            foreach ($areas as $area) {
                $areaOlimpiadas[$area->nombre_area] = AreaOlimpiada::firstOrCreate([
                    'id_area' => $area->id_area,
                    'id_olimpiada' => $olimpiada->id_olimpiada
                ]);
            }

            // 4. Crear Configuraciones Area-Nivel
            $areaNiveles = [];
            
            // Para Matemáticas
            if (isset($areaOlimpiadas['Matemáticas'])) {
                $ao = $areaOlimpiadas['Matemáticas'];
                $areaNiveles['Matemáticas_1ro'] = AreaNivel::firstOrCreate([
                    'id_area_olimpiada' => $ao->id_area_olimpiada,
                    'id_nivel' => $nivelSecundaria->id_nivel
                ], ['es_activo_area_nivel' => true]);
            }

            // Para Física
            if (isset($areaOlimpiadas['Física'])) {
                $ao = $areaOlimpiadas['Física'];
                $areaNiveles['Física_1ro'] = AreaNivel::firstOrCreate([
                    'id_area_olimpiada' => $ao->id_area_olimpiada,
                    'id_nivel' => $nivelSecundaria->id_nivel
                ], ['es_activo_area_nivel' => true]);
            }

            // 5. Crear Usuarios Responsables y Evaluadores
            $rolResponsable = Rol::where('nombre_rol', 'Responsable Area')->first();
            $rolEvaluador = Rol::where('nombre_rol', 'Evaluador')->first();

            // Responsable Matemáticas
            $persResp = Persona::create([
                'nombre_pers' => 'Carlos', 'apellido_pers' => 'Perez', 
                'ci_pers' => '9988776', 'email_pers' => 'carlos.perez@test.com', 
                'telefono_pers' => '77788899'
            ]);
            $userResp = Usuario::create([
                'id_persona' => $persResp->id_persona,
                'email_usuario' => 'carlos.perez@test.com',
                'password_usuario' => Hash::make('mundolibre')
            ]);
            $userResp->roles()->attach($rolResponsable->id_rol, ['id_olimpiada' => $olimpiada->id_olimpiada]);
            
            if (isset($areaOlimpiadas['Matemáticas'])) {
                ResponsableArea::create([
                    'id_usuario' => $userResp->id_usuario,
                    'id_area_olimpiada' => $areaOlimpiadas['Matemáticas']->id_area_olimpiada
                ]);
            }

            // Evaluador Matemáticas
            $persEval = Persona::create([
                'nombre_pers' => 'Lucia', 'apellido_pers' => 'Mendez', 
                'ci_pers' => '6655443', 'email_pers' => 'lucia.mendez@test.com', 
                'telefono_pers' => '77788800'
            ]);
            $userEval = Usuario::create([
                'id_persona' => $persEval->id_persona,
                'email_usuario' => 'lucia.mendez@test.com',
                'password_usuario' => Hash::make('password12')
            ]);
            $userEval->roles()->attach($rolEvaluador->id_rol, ['id_olimpiada' => $olimpiada->id_olimpiada]);
            
            $evaluadorAnMat = null;
            if (isset($areaNiveles['Matemáticas_1ro'])) {
                $evaluadorAnMat = EvaluadorAn::create([
                    'id_usuario' => $userEval->id_usuario,
                    'id_area_nivel' => $areaNiveles['Matemáticas_1ro']->id_area_nivel,
                    'estado_eva_an' => true
                ]);
            }

            $this->command->info('Usuarios creados y asignados.');

            // 6. Crear Competidores e Inscripciones
            $institucion = Institucion::firstOrCreate(['nombre_inst' => 'Colegio Don Bosco']);
            $depto = Departamento::firstOrCreate(['nombre_dep' => 'La Paz']);
            
            $competidoresData = [
                ['nombre' => 'Ana', 'apellido' => 'Vaca', 'ci' => '1234567', 'nota' => 95.5],
                ['nombre' => 'Juan', 'apellido' => 'Angel', 'ci' => '2345678', 'nota' => 88.0],
                ['nombre' => 'Sofia', 'apellido' => 'Rios', 'ci' => '3456789', 'nota' => 76.5],
                ['nombre' => 'Mateo', 'apellido' => 'Choque', 'ci' => '4567890', 'nota' => 45.0],
            ];

            $inscripcionesCreadas = [];

            if (isset($areaNiveles['Matemáticas_1ro'])) {
                foreach ($competidoresData as $data) {
                    // Persona
                    $p = Persona::create([
                        'nombre_pers' => $data['nombre'], 'apellido_pers' => $data['apellido'],
                        'ci_pers' => $data['ci'], 'email_pers' => strtolower($data['nombre']).'@test2023.com',
                        'telefono_pers' => '0000000'
                    ]);

                    // Competidor
                    $comp = Competidor::create([
                        'id_persona' => $p->id_persona,
                        'id_institucion' => $institucion->id_institucion,
                        'id_departamento' => $depto->id_departamento,
                        'id_grado_escolaridad' => $grado1ro->id_grado_escolaridad,
                        'genero_competidor' => 'M',
                        'contacto_tutor_compe' => 'Tutor 2023'
                    ]);

                    // Inscripción
                    $insc = Inscripcion::create([
                        'id_competidor' => $comp->id_competidor,
                        'id_area_nivel' => $areaNiveles['Matemáticas_1ro']->id_area_nivel
                    ]);
                    
                    $inscripcionesCreadas[] = ['inscripcion' => $insc, 'nota' => $data['nota']];
                }

                // 7. Crear Competencia (Examen Final)
                $faseFinal = FaseGlobal::firstOrCreate(['codigo_fas_glo' => 'F3_NAC'], [
                    'nombre_fas_glo' => 'Etapa Nacional', 'orden_fas_glo' => 3
                ]);

                $competencia = Competencia::create([
                    'id_fase_global' => $faseFinal->id_fase_global,
                    'id_area_nivel' => $areaNiveles['Matemáticas_1ro']->id_area_nivel,
                    'nombre_examen' => 'Examen Final Matemáticas 2023',
                    'fecha_inicio' => Carbon::create(2023, 10, 15, 9, 0, 0),
                    'fecha_fin' => Carbon::create(2023, 10, 15, 12, 0, 0),
                    'estado_comp' => false
                ]);

                // 8. Registrar Evaluaciones
                if ($evaluadorAnMat) {
                    foreach ($inscripcionesCreadas as $item) {
                        Evaluacion::create([
                            'id_inscripcion' => $item['inscripcion']->id_inscripcion,
                            'id_competencia' => $competencia->id_competencia,
                            'id_evaluador_an' => $evaluadorAnMat->id_evaluador_an,
                            'nota_evalu' => $item['nota'],
                            'estado_competidor_eva' => $item['nota'] > 51 ? 'APROBADO' : 'REPROBADO',
                            'fecha_evalu' => Carbon::create(2023, 10, 20),
                            'estado_evalu' => true
                        ]);
                    }
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
            }

            $this->command->info('✅ Seeder de Olimpiada 2023 completado.');
        });
    }
}