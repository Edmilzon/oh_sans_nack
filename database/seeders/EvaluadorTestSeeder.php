<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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

class EvaluadorTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('Iniciando EvaluadorTestSeeder...');

            // --- 1. Crear o encontrar el usuario evaluador ---
            $evaluadorUser = Usuario::firstOrCreate(
                ['ci' => '8888888'],
                [
                    'nombre' => 'Clao',
                    'apellido' => 'Test',
                    'email' => 'clao@gmail.com',
                    'password' => bcrypt('claotest'),
                    'telefono' => '78888888'
                ]
            );
            $this->command->info("Usuario evaluador '{$evaluadorUser->nombre} {$evaluadorUser->apellido}' creado/encontrado.");

            // --- 2. Obtener o crear entidades necesarias ---
            $olimpiada2025 = Olimpiada::firstOrCreate(['gestion' => '2025'], ['nombre' => 'Olimpiada Científica 2025']);
            $areaMatematicas = Area::firstOrCreate(['nombre' => 'Matemáticas']);
            $areaFisica = Area::firstOrCreate(['nombre' => 'Física']);
            $nivel1 = Nivel::firstOrCreate(['nombre' => 'primero de Secundaria']);
            $nivel2 = Nivel::firstOrCreate(['nombre' => 'segundo de Secundaria']);
            $nivel3 = Nivel::firstOrCreate(['nombre' => 'tercero de Secundaria']);
            $grado2do = GradoEscolaridad::firstOrCreate(['nombre' => '2ro de Secundaria']);
            $rolEvaluador = Rol::where('nombre', 'Evaluador')->first();

            if (!$rolEvaluador) {
                $this->command->error('El rol "Evaluador" no existe. Ejecuta RolesSeeder primero.');
                return;
            }

            // --- 3. Asignar rol de Evaluador para la gestión 2025 ---
            DB::table('usuario_rol')->insertOrIgnore([
                'id_usuario' => $evaluadorUser->id_usuario,
                'id_rol' => $rolEvaluador->id_rol,
                'id_olimpiada' => $olimpiada2025->id_olimpiada,
            ]);

            // --- 4. Crear las combinaciones de AreaNivel y asignarlas ---
            $asignaciones = [
                // Matemáticas
                ['id_area' => $areaMatematicas->id_area, 'id_nivel' => $nivel1->id_nivel, 'id_grado_escolaridad' => $grado2do->id_grado_escolaridad],
                ['id_area' => $areaMatematicas->id_area, 'id_nivel' => $nivel1->id_nivel, 'id_grado_escolaridad' => $grado2do->id_grado_escolaridad],

                ['id_area' => $areaMatematicas->id_area, 'id_nivel' => $nivel2->id_nivel, 'id_grado_escolaridad' => $grado2do->id_grado_escolaridad],
                ['id_area' => $areaMatematicas->id_area, 'id_nivel' => $nivel2->id_nivel, 'id_grado_escolaridad' => $grado2do->id_grado_escolaridad],

                ['id_area' => $areaMatematicas->id_area, 'id_nivel' => $nivel3->id_nivel, 'id_grado_escolaridad' => $grado2do->id_grado_escolaridad],
                ['id_area' => $areaMatematicas->id_area, 'id_nivel' => $nivel3->id_nivel, 'id_grado_escolaridad' => $grado2do->id_grado_escolaridad],
                // Física
                ['id_area' => $areaFisica->id_area, 'id_nivel' => $nivel1->id_nivel, 'id_grado_escolaridad' => $grado2do->id_grado_escolaridad],
                ['id_area' => $areaFisica->id_area, 'id_nivel' => $nivel1->id_nivel, 'id_grado_escolaridad' => $grado2do->id_grado_escolaridad],
                
                ['id_area' => $areaFisica->id_area, 'id_nivel' => $nivel2->id_nivel, 'id_grado_escolaridad' => $grado2do->id_grado_escolaridad],
                ['id_area' => $areaFisica->id_area, 'id_nivel' => $nivel2->id_nivel, 'id_grado_escolaridad' => $grado2do->id_grado_escolaridad],

                
            ];

            foreach ($asignaciones as $asignacion) {
                // Crear la entrada en area_nivel
                $areaNivel = AreaNivel::firstOrCreate([
                    'id_area' => $asignacion['id_area'],
                    'id_nivel' => $asignacion['id_nivel'],
                    'id_grado_escolaridad' => $asignacion['id_grado_escolaridad'],
                    'id_olimpiada' => $olimpiada2025->id_olimpiada,
                ]);

                // Asignar al evaluador
                EvaluadorAn::firstOrCreate([
                    'id_usuario' => $evaluadorUser->id_usuario,
                    'id_area_nivel' => $areaNivel->id_area_nivel,
                ]);
            }

            // --- 5. Crear Instituciones para los competidores ---
            $institucion1 = Institucion::firstOrCreate(['nombre' => 'Colegio Don Bosco (TEST)']);
            $institucion2 = Institucion::firstOrCreate(['nombre' => 'Colegio La Salle (TEST)']);
            $this->command->info('Instituciones para competidores de prueba creadas/encontradas.');

            // --- 6. Crear Personas para los competidores ---
            $personaCompetidor1 = Persona::firstOrCreate(
                ['ci' => '11122233'],
                ['nombre' => 'Juan', 'apellido' => 'Perez', 'email' => 'juan.perez.test@test.com', 'genero' => 'M', 'telefono' => '71112233']
            );
            $personaCompetidor2 = Persona::firstOrCreate(
                ['ci' => '44455566'],
                ['nombre' => 'Ana', 'apellido' => 'Gomez', 'email' => 'ana.gomez.test@test.com', 'genero' => 'F', 'telefono' => '74445566']
            );
            $this->command->info('Personas para competidores de prueba creadas/encontradas.');
            
            // --- 6.1 Crear MUCHAS más Personas para competidores ---
            $nuevasPersonasData = [
                ['ci' => '20000001', 'nombre' => 'Carlos', 'apellido' => 'Solis', 'email' => 'carlos.solis.test@test.com', 'genero' => 'M', 'telefono' => '72000001'],
                ['ci' => '20000002', 'nombre' => 'Maria', 'apellido' => 'Luna', 'email' => 'maria.luna.test@test.com', 'genero' => 'F', 'telefono' => '72000002'],
                ['ci' => '20000003', 'nombre' => 'Pedro', 'apellido' => 'Arias', 'email' => 'pedro.arias.test@test.com', 'genero' => 'M', 'telefono' => '72000003'],
                ['ci' => '20000004', 'nombre' => 'Lucia', 'apellido' => 'Campos', 'email' => 'lucia.campos.test@test.com', 'genero' => 'F', 'telefono' => '72000004'],
                ['ci' => '20000005', 'nombre' => 'Jorge', 'apellido' => 'Vargas', 'email' => 'jorge.vargas.test@test.com', 'genero' => 'M', 'telefono' => '72000005'],
                ['ci' => '20000006', 'nombre' => 'Sofia', 'apellido' => 'Mendoza', 'email' => 'sofia.mendoza.test@test.com', 'genero' => 'F', 'telefono' => '72000006'],
                ['ci' => '20000007', 'nombre' => 'Luis', 'apellido' => 'Rojas', 'email' => 'luis.rojas.test@test.com', 'genero' => 'M', 'telefono' => '72000007'],
                ['ci' => '20000008', 'nombre' => 'Elena', 'apellido' => 'Paz', 'email' => 'elena.paz.test@test.com', 'genero' => 'F', 'telefono' => '72000008'],
                ['ci' => '20000009', 'nombre' => 'Miguel', 'apellido' => 'Suarez', 'email' => 'miguel.suarez.test@test.com', 'genero' => 'M', 'telefono' => '72000009'],
                ['ci' => '20000010', 'nombre' => 'Isabel', 'apellido' => 'Castillo', 'email' => 'isabel.castillo.test@test.com', 'genero' => 'F', 'telefono' => '72000010'],
            ];

            // --- 7. Crear Competidores ---
            // Obtener el primer AreaNivel de Matemáticas creado anteriormente
            $areaNivelMat = AreaNivel::where('id_area', $areaMatematicas->id_area)
                ->where('id_olimpiada', $olimpiada2025->id_olimpiada)
                ->first();

            // Obtener el primer AreaNivel de Física creado anteriormente
            $areaNivelFis = AreaNivel::where('id_area', $areaFisica->id_area)
                ->where('id_olimpiada', $olimpiada2025->id_olimpiada)
                ->first();

            if ($areaNivelMat) {
                Competidor::firstOrCreate(
                    ['id_persona' => $personaCompetidor1->id_persona, 'id_area_nivel' => $areaNivelMat->id_area_nivel],
                    [
                        'departamento' => 'La Paz',
                        'id_grado_escolaridad' => $areaNivelMat->id_grado_escolaridad,
                        'id_institucion' => $institucion1->id_institucion,
                    ]
                );
            }

            if ($areaNivelFis) {
                Competidor::firstOrCreate(
                    ['id_persona' => $personaCompetidor2->id_persona, 'id_area_nivel' => $areaNivelFis->id_area_nivel],
                    [
                        'departamento' => 'Cochabamba',
                        'id_grado_escolaridad' => $areaNivelFis->id_grado_escolaridad,
                        'id_institucion' => $institucion2->id_institucion,
                    ]
                );
            }

            $this->command->info('Competidores de prueba iniciales creados.');

            // --- 7.1 Crear MUCHOS más Competidores ---
            $this->command->info('Creando competidores adicionales...');
            $areaNivelesDisponibles = AreaNivel::where('id_olimpiada', $olimpiada2025->id_olimpiada)->get();
            $instituciones = [$institucion1, $institucion2];
            $departamentos = ['La Paz', 'Cochabamba', 'Santa Cruz', 'Oruro', 'Potosí', 'Chuquisaca', 'Tarija', 'Beni', 'Pando'];

            if ($areaNivelesDisponibles->isNotEmpty()) {
                foreach ($nuevasPersonasData as $i => $personaData) {
                    $persona = Persona::firstOrCreate(['ci' => $personaData['ci']], $personaData);

                    // Asignar de forma rotativa
                    $areaNivelAsignado = $areaNivelesDisponibles[$i % $areaNivelesDisponibles->count()];
                    $institucionAsignada = $instituciones[$i % count($instituciones)];
                    $departamentoAsignado = $departamentos[$i % count($departamentos)];

                    $competidor = Competidor::firstOrCreate(
                        ['id_persona' => $persona->id_persona, 'id_area_nivel' => $areaNivelAsignado->id_area_nivel],
                        [
                            'departamento' => $departamentoAsignado,
                            'id_grado_escolaridad' => $areaNivelAsignado->id_grado_escolaridad,
                            'id_institucion' => $institucionAsignada->id_institucion,
                        ]
                    );

                }
                $this->command->info(count($nuevasPersonasData) . ' competidores adicionales han sido creados.');
            }

            $this->command->info('Asignaciones de áreas y niveles para el evaluador completadas.');
            $this->command->info('EvaluadorTestSeeder completado exitosamente!');
        });
    }
}
