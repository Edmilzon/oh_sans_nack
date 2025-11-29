<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Model\Usuario;
use App\Model\Olimpiada;
use App\Model\Area;
use App\Model\Nivel;
use App\Model\GradoEscolaridad;
use App\Model\Rol;
use App\Model\AreaNivel;
use App\Model\EvaluadorAn;
use App\Model\Institucion;
use App\Model\Persona;
use App\Model\Competidor;
use App\Model\Departamento;
use App\Model\Inscripcion;

class EvaluadorTestSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('Iniciando EvaluadorTestSeeder V8...');

            // --- 1. Crear o encontrar el usuario evaluador ---
            // Paso A: Persona
            $personaEvaluador = Persona::firstOrCreate(
                ['ci_pers' => '8888888'],
                [
                    'nombre_pers' => 'Clao',
                    'apellido_pers' => 'Test',
                    'email_pers' => 'clao@gmail.com',
                    'telefono_pers' => '78888888'
                ]
            );

            // Paso B: Usuario
            $evaluadorUser = Usuario::firstOrCreate(
                ['email_usuario' => 'clao@gmail.com'],
                [
                    'id_persona' => $personaEvaluador->id_persona,
                    'password_usuario' => Hash::make('claotest')
                ]
            );
            
            $this->command->info("Usuario evaluador '{$personaEvaluador->nombre_pers}' listo.");

            // --- 2. Obtener o crear entidades necesarias ---
            $olimpiada2025 = Olimpiada::firstOrCreate(
                ['gestion_olimp' => '2025'], 
                ['nombre_olimp' => 'Olimpiada Científica 2025', 'estado_olimp' => true]
            );

            // Áreas
            $areaMatematicas = Area::firstOrCreate(['nombre_area' => 'Matemáticas']);
            $areaFisica = Area::firstOrCreate(['nombre_area' => 'Física']);
            
            // Niveles
            $nivel1 = Nivel::firstOrCreate(['nombre_nivel' => 'Primaria']);
            $nivel2 = Nivel::firstOrCreate(['nombre_nivel' => 'Secundaria']);
            
            // Grados
            $grado2do = GradoEscolaridad::firstOrCreate(['nombre_grado' => '2do de Secundaria']);
            
            // Rol
            $rolEvaluador = Rol::where('nombre_rol', 'Evaluador')->first();
            if (!$rolEvaluador) {
                $this->command->error('El rol "Evaluador" no existe. Ejecuta RolesSeeder primero.');
                return;
            }

            // --- 3. Asignar rol de Evaluador para la gestión 2025 ---
            // Usamos la relación del modelo para evitar conflictos de duplicados
            if (!$evaluadorUser->tieneRol('Evaluador', $olimpiada2025->id_olimpiada)) {
                $evaluadorUser->roles()->attach($rolEvaluador->id_rol, [
                    'id_olimpiada' => $olimpiada2025->id_olimpiada,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // --- 4. Configurar AreaOlimpiada y AreaNivel ---
            // Definimos qué asignaciones vamos a crear
            $asignaciones = [
                ['area' => $areaMatematicas, 'nivel' => $nivel1], // Matematicas - Primaria
                ['area' => $areaMatematicas, 'nivel' => $nivel2], // Matematicas - Secundaria
                ['area' => $areaFisica,      'nivel' => $nivel2], // Fisica - Secundaria
            ];

            foreach ($asignaciones as $asignacion) {
                // A. Area -> Olimpiada
                $areaOlimp = DB::table('area_olimpiada')->where([
                    'id_area' => $asignacion['area']->id_area,
                    'id_olimpiada' => $olimpiada2025->id_olimpiada
                ])->first();

                if (!$areaOlimp) {
                    $idAreaOlimp = DB::table('area_olimpiada')->insertGetId([
                        'id_area' => $asignacion['area']->id_area,
                        'id_olimpiada' => $olimpiada2025->id_olimpiada,
                        'created_at' => now(), 'updated_at' => now()
                    ]);
                } else {
                    $idAreaOlimp = $areaOlimp->id_area_olimpiada;
                }

                // B. AreaOlimpiada -> Nivel (AreaNivel)
                $areaNivel = AreaNivel::firstOrCreate([
                    'id_area_olimpiada' => $idAreaOlimp,
                    'id_nivel' => $asignacion['nivel']->id_nivel
                ], [
                    'es_activo_area_nivel' => true
                ]);

                // C. Asignar al evaluador a este AreaNivel
                EvaluadorAn::firstOrCreate([
                    'id_usuario' => $evaluadorUser->id_usuario,
                    'id_area_nivel' => $areaNivel->id_area_nivel,
                ], ['estado_eva_an' => true]);
            }

            // --- 5. Crear Instituciones y Deptos ---
            $institucion1 = Institucion::firstOrCreate(['nombre_inst' => 'Colegio Don Bosco (TEST)']);
            $deptoLP = Departamento::firstOrCreate(['nombre_dep' => 'La Paz']);

            // --- 6. Crear Personas para Competidores ---
            $personasData = [
                ['ci' => '11122233', 'nombre' => 'Juan', 'apellido' => 'Perez', 'email' => 'juan.test@test.com', 'genero' => 'M'],
                ['ci' => '44455566', 'nombre' => 'Ana', 'apellido' => 'Gomez', 'email' => 'ana.test@test.com', 'genero' => 'F'],
                ['ci' => '20000001', 'nombre' => 'Carlos', 'apellido' => 'Solis', 'email' => 'carlos.test@test.com', 'genero' => 'M'],
            ];

            // --- 7. Crear Competidores e Inscripciones ---
            
            // Recuperamos un area_nivel válido (ej: Matematicas Secundaria)
            // Buscamos a través de las tablas para ser precisos
            $idMatematica = $areaMatematicas->id_area;
            $idSecundaria = $nivel2->id_nivel;
            
            $areaNivelTarget = AreaNivel::whereHas('areaOlimpiada', function($q) use ($idMatematica, $olimpiada2025) {
                $q->where('id_area', $idMatematica)->where('id_olimpiada', $olimpiada2025->id_olimpiada);
            })->where('id_nivel', $idSecundaria)->first();

            if ($areaNivelTarget) {
                foreach ($personasData as $pData) {
                    // A. Persona
                    $pers = Persona::firstOrCreate(
                        ['ci_pers' => $pData['ci']],
                        [
                            'nombre_pers' => $pData['nombre'],
                            'apellido_pers' => $pData['apellido'],
                            'email_pers' => $pData['email'],
                            'telefono_pers' => '00000000'
                        ]
                    );

                    // B. Competidor (Perfil)
                    $comp = Competidor::firstOrCreate(
                        ['id_persona' => $pers->id_persona],
                        [
                            'id_institucion' => $institucion1->id_institucion,
                            'id_departamento' => $deptoLP->id_departamento,
                            'id_grado_escolaridad' => $grado2do->id_grado_escolaridad, // 2do Secundaria
                            'genero_competidor' => $pData['genero'],
                            'contacto_tutor_compe' => 'Padre Test'
                        ]
                    );

                    // C. Inscripción (Vinculación al AreaNivel)
                    Inscripcion::firstOrCreate([
                        'id_competidor' => $comp->id_competidor,
                        'id_area_nivel' => $areaNivelTarget->id_area_nivel
                    ]);
                }
                $this->command->info('Competidores e inscripciones creadas en Matemáticas-Secundaria.');
            } else {
                $this->command->warn('No se pudo encontrar el AreaNivel objetivo para inscribir alumnos.');
            }

            $this->command->info('EvaluadorTestSeeder V8 completado exitosamente!');
        });
    }
}