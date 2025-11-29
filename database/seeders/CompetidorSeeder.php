<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\Competidor;
use App\Model\Persona;
use App\Model\Institucion;
use App\Model\Departamento;
use App\Model\AreaNivel;
use App\Model\GradoEscolaridad;
use App\Model\ArchivoCsv;
use App\Model\Inscripcion;
use Faker\Factory as Faker;

class CompetidorSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // 1️⃣ Departamentos (Ya deberían estar creados por DepartamentoSeeder, los buscamos)
        $departamentos = Departamento::pluck('id_departamento')->toArray();
        if(empty($departamentos)){
             $this->command->error('Ejecuta DepartamentoSeeder primero.'); return;
        }

        // 2️⃣ Instituciones
        if (Institucion::count() == 0) {
            $institucionesDummy = ['Colegio Nacional Sucre', 'Unidad Educativa Santa Cruz', 'Instituto Simón Bolívar'];
            foreach ($institucionesDummy as $nombre) {
                Institucion::create(['nombre_inst' => $nombre]);
            }
        }
        $instituciones = Institucion::pluck('id_institucion')->toArray();

        // 3️⃣ Archivos CSV
        if (ArchivoCsv::count() == 0) {
            for ($i = 1; $i <= 3; $i++) {
                ArchivoCsv::create([
                    'nombre_arc_csv' => "Importacion_$i.csv",
                    'fecha_arc_csv' => $faker->date(),
                ]);
            }
        }
        $archivos = ArchivoCsv::pluck('id_archivo_csv')->toArray();

        // 4️⃣ Áreas y Grados
        $areasNiveles = AreaNivel::pluck('id_area_nivel')->toArray();
        $grados = GradoEscolaridad::pluck('id_grado_escolaridad')->toArray();

        if (empty($areasNiveles) || empty($grados)) {
            $this->command->error('Faltan datos en area_nivel o grado_escolaridad.');
            return;
        }

        // 5️⃣ Crear Competidores e Inscripciones
        $numCompetidores = 50; // Reduje el número para que sea más rápido en pruebas
        
        for ($i = 1; $i <= $numCompetidores; $i++) {
            // A. Crear Persona
            $persona = Persona::create([
                'nombre_pers' => $faker->firstName,
                'apellido_pers' => $faker->lastName,
                'ci_pers' => $faker->unique()->numberBetween(1000000, 9999999),
                'telefono_pers' => $faker->phoneNumber,
                'email_pers' => $faker->unique()->safeEmail,
            ]);

            // B. Crear Competidor (Perfil)
            $grado = $faker->randomElement($grados);
            $competidor = Competidor::create([
                'id_persona' => $persona->id_persona,
                'id_institucion' => $faker->randomElement($instituciones),
                'id_departamento' => $faker->randomElement($departamentos),
                'id_grado_escolaridad' => $grado,
                'id_archivo_csv' => $faker->randomElement($archivos),
                'contacto_tutor_compe' => $faker->name,
                'genero_competidor' => $faker->randomElement(['M', 'F']),
            ]);

            // C. Crear Inscripción (Asignarlo a un área/nivel)
            // Lógica simple: lo inscribimos en un área aleatoria
            $areaNivel = $faker->randomElement($areasNiveles);
            
            Inscripcion::create([
                'id_competidor' => $competidor->id_competidor,
                'id_area_nivel' => $areaNivel,
            ]);
        }

        $this->command->info('Competidores e inscripciones creados correctamente.');
    }
}