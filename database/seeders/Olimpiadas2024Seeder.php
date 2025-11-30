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

class Olimpiadas2024Seeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('🚀 Iniciando seeder para la Olimpiada 2024 (V8)...');

            // 1. Crear la Olimpiada 2024
            $olimpiada = Olimpiada::firstOrCreate(
                ['gestion_olimp' => '2024'],
                ['nombre_olimp' => 'Olimpiada Científica Estudiantil 2024', 'estado_olimp' => true] // Activa para pruebas
            );
            $this->command->info("Olimpiada '{$olimpiada->nombre_olimp}' creada.");

            // 2. Obtener o crear Área Química y Niveles
            $areaQuimica = Area::firstOrCreate(['nombre_area' => 'Química']);
            $nivelSecundaria = Nivel::firstOrCreate(['nombre_nivel' => 'Secundaria']); // Ajustado a V8

            // Grados
            $grado1ro = GradoEscolaridad::firstOrCreate(['nombre_grado' => '1ro de Secundaria']);
            $grado2do = GradoEscolaridad::firstOrCreate(['nombre_grado' => '2do de Secundaria']);

            // 3. Vincular Química con la Olimpiada 2024
            $areaOlimpiadaQuimica = AreaOlimpiada::firstOrCreate([
                'id_area' => $areaQuimica->id_area,
                'id_olimpiada' => $olimpiada->id_olimpiada,
            ]);

            // 4. Crear AreaNivel para Química (para 1ro y 2do de secundaria)
            // Nota: En V8, los grados se asocian al competidor, no al AreaNivel directamente.
            // AreaNivel define "Química Secundaria".
            
            $areaNivelQuimica = AreaNivel::firstOrCreate([
                'id_area_olimpiada' => $areaOlimpiadaQuimica->id_area_olimpiada,
                'id_nivel' => $nivelSecundaria->id_nivel
            ], ['es_activo_area_nivel' => true]);

            // 5. Crear Personas primero
            $personasData = [
                ['nombre' => 'Roberto', 'apellido' => 'Gomez', 'ci' => '9988777', 'email' => 'roberto.gomez@test.com', 'genero' => 'M', 'telefono' => '77788877'],
                ['nombre' => 'Mariana', 'apellido' => 'Salas', 'ci' => '6655444', 'email' => 'mariana.salas@test.com', 'genero' => 'F', 'telefono' => '77788844'],
                ['nombre' => 'Pedro', 'apellido' => 'Lopez', 'ci' => '5678901', 'email' => 'pedro.lopez@test.com', 'genero' => 'M', 'telefono' => '77711117'],
                ['nombre' => 'Juan', 'apellido' => 'Tiburcio', 'ci' => '6789020', 'email' => 'juan.tiburcio@test.com', 'genero' => 'M', 'telefono' => '77711122'],
            ];

            $personas = [];
            foreach ($personasData as $data) {
                $personas[] = Persona::firstOrCreate(
                    ['ci_pers' => $data['ci']],
                    [
                        'nombre_pers' => $data['nombre'],
                        'apellido_pers' => $data['apellido'],
                        'email_pers' => $data['email'],
                        'telefono_pers' => $data['telefono']
                    ]
                );
            }

            // 6. Crear usuarios responsables y evaluadores para Química
            $responsableUser = Usuario::firstOrCreate(['email_usuario' => 'roberto.gomez@test.com'], [
                'id_persona' => $personas[0]->id_persona,
                'password_usuario' => Hash::make('password123'),
            ]);

            $evaluadorUser = Usuario::firstOrCreate(['email_usuario' => 'mariana.salas@test.com'], [
                'id_persona' => $personas[1]->id_persona,
                'password_usuario' => Hash::make('password123'),
            ]);

            // Asignar roles
            $rolResponsable = Rol::where('nombre_rol', 'Responsable Area')->first();
            $rolEvaluador = Rol::where('nombre_rol', 'Evaluador')->first();

            if ($rolResponsable && $rolEvaluador) {
                $responsableUser->roles()->syncWithoutDetaching([$rolResponsable->id_rol => ['id_olimpiada' => $olimpiada->id_olimpiada]]);
                $evaluadorUser->roles()->syncWithoutDetaching([$rolEvaluador->id_rol => ['id_olimpiada' => $olimpiada->id_olimpiada]]);
            }

            ResponsableArea::firstOrCreate([
                'id_usuario' => $responsableUser->id_usuario,
                'id_area_olimpiada' => $areaOlimpiadaQuimica->id_area_olimpiada
            ]);

            $evaluadorQuimica = EvaluadorAn::firstOrCreate([
                'id_usuario' => $evaluadorUser->id_usuario,
                'id_area_nivel' => $areaNivelQuimica->id_area_nivel
            ], ['estado_eva_an' => true]);

            $this->command->info('Responsable y evaluador asignados a Química.');

            // 7. Crear Instituciones y Depto
            $institucion1 = Institucion::firstOrCreate(['nombre_inst' => 'Colegio San Agustín']);
            $institucion2 = Institucion::firstOrCreate(['nombre_inst' => 'Colegio Alemán']);
            $depto = Departamento::firstOrCreate(['nombre_dep' => 'La Paz']);

            // 8. Crear competidores e inscripciones para Química
            // Pedro Lopez (1ro Sec) y Juan Tiburcio (2do Sec)
            $competidoresQuimicaData = [
                [
                    'id_persona' => $personas[2]->id_persona,
                    'id_institucion' => $institucion1->id_institucion,
                    'id_departamento' => $depto->id_departamento,
                    'id_grado_escolaridad' => $grado1ro->id_grado_escolaridad,
                    'contacto_tutor_compe' => '77722230',
                    'genero_competidor' => 'M'
                ],
                [
                    'id_persona' => $personas[3]->id_persona,
                    'id_institucion' => $institucion2->id_institucion,
                    'id_departamento' => $depto->id_departamento,
                    'id_grado_escolaridad' => $grado2do->id_grado_escolaridad,
                    'contacto_tutor_compe' => '77722231',
                    'genero_competidor' => 'M'
                ],
            ];

            $inscripcionesCreadas = [];

            foreach ($competidoresQuimicaData as $data) {
                $comp = Competidor::create($data);
                
                // Inscribir en Química
                $insc = Inscripcion::create([
                    'id_competidor' => $comp->id_competidor,
                    'id_area_nivel' => $areaNivelQuimica->id_area_nivel
                ]);
                
                $inscripcionesCreadas[] = $insc;
            }

            $this->command->info('Competidores de Química inscritos.');

            // 9. Crear competencia final (Usando FaseGlobal)
            $faseFinal = FaseGlobal::firstOrCreate(['codigo_fas_glo' => 'F3_NAC'], [
                'nombre_fas_glo' => 'Etapa Nacional', 'orden_fas_glo' => 3
            ]);

            $competencia = Competencia::create([
                'id_fase_global' => $faseFinal->id_fase_global,
                'id_area_nivel' => $areaNivelQuimica->id_area_nivel,
                'nombre_examen' => 'Examen Nacional Química 2024',
                'fecha_inicio' => Carbon::create(2024, 11, 1, 8, 0, 0),
                'fecha_fin' => Carbon::create(2024, 11, 1, 12, 0, 0),
                'estado_comp' => true // En curso
            ]);

            // 10. Crear evaluaciones
            $notas = [90, 85];
            foreach ($inscripcionesCreadas as $index => $insc) {
                Evaluacion::create([
                    'id_inscripcion' => $insc->id_inscripcion,
                    'id_competencia' => $competencia->id_competencia,
                    'id_evaluador_an' => $evaluadorQuimica->id_evaluador_an,
                    'nota_evalu' => $notas[$index],
                    'estado_competidor_eva' => 'APROBADO',
                    'fecha_evalu' => now(),
                    'estado_evalu' => true
                ]);
            }

            $this->command->info('Evaluaciones de Química registradas.');

            // 11. Medallero
            foreach ($inscripcionesCreadas as $index => $insc) {
                Medallero::create([
                    'id_inscripcion' => $insc->id_inscripcion,
                    'id_competencia' => $competencia->id_competencia,
                    'puesto_medall' => $index + 1,
                    'medalla_medall' => $index == 0 ? 'ORO' : 'PLATA'
                ]);
            }

            $this->command->info('Medallero de Química generado.');
            $this->command->info('¡Seeder Olimpiada 2024 completado exitosamente!');
        });
    }
}