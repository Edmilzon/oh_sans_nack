<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\Competidor;
use App\Model\Persona;
use App\Model\Institucion;
use App\Model\AreaNivel;
use App\Model\GradoEscolaridad;
use App\Model\ArchivoCsv;
use App\Model\Olimpiada;
use App\Model\Departamento;
use Faker\Factory as Faker;

class CompetidorSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // 1. Obtener Olimpiada Actual
        $olimpiada = Olimpiada::where('gestion', date('Y'))->first()
                     ?? Olimpiada::latest('id_olimpiada')->first();

        // 2. Departamentos
        $idsDepartamentos = Departamento::pluck('id_departamento')->toArray();
        if (empty($idsDepartamentos)) {
            $this->command->warn('⚠️ No hay departamentos en la BD. Ejecuta DepartamentoSeeder. Usando valores aleatorios 1-9.');
            $idsDepartamentos = range(1, 9);
        }

        // 3. Instituciones
        if (Institucion::count() == 0) {
            $institucionesDummy = ['Colegio A', 'Colegio B', 'Colegio C'];
            foreach ($institucionesDummy as $nombre) {
                Institucion::firstOrCreate(['nombre' => $nombre]);
            }
        }
        $idsInstituciones = Institucion::pluck('id_institucion')->toArray();

        // 4. Archivos CSV
        if (ArchivoCsv::count() == 0) {
            ArchivoCsv::create(['nombre' => "import_test.csv", 'fecha' => now()]);
        }
        $idsArchivos = ArchivoCsv::pluck('id_archivo_csv')->toArray();

        // 5. Datos Académicos
        $idsAreasNiveles = AreaNivel::pluck('id_area_nivel')->toArray();
        $idsGrados = GradoEscolaridad::pluck('id_grado_escolaridad')->toArray();

        if (empty($idsAreasNiveles) || empty($idsGrados)) {
            $this->command->error('❌ Faltan datos en area_nivel o grado_escolaridad.');
            return;
        }

        // 6. Crear Competidores
        $cantidad = 50;
        $this->command->info("Generando $cantidad competidores...");
        $this->command->getOutput()->progressStart($cantidad);

        for ($i = 0; $i < $cantidad; $i++) {

            // A. Crear Persona (SIN género en la tabla persona)
            $generoReal = $faker->randomElement(['M', 'F']);

            $persona = Persona::create([
                'nombre' => $faker->firstName($generoReal == 'M' ? 'male' : 'female'),
                'apellido' => $faker->lastName,
                'ci' => $faker->unique()->numerify('#######'),
                'telefono' => $faker->numerify('7#######'),
                'email' => $faker->unique()->safeEmail,
                // 'genero' => ... ELIMINADO (No existe en tabla persona)
            ]);

            // B. Crear Competidor
            // TRUCO SENIOR: substr() para asegurar que no pasemos de 15 caracteres
            $nombreTutor = substr($faker->firstName, 0, 15);

            Competidor::create([
                'id_persona'           => $persona->id_persona,
                'id_institucion'       => $faker->randomElement($idsInstituciones),
                'id_departamento'      => $faker->randomElement($idsDepartamentos),
                'id_area_nivel'        => $faker->randomElement($idsAreasNiveles),
                'id_grado_escolaridad' => $faker->randomElement($idsGrados),
                'id_archivo_csv'       => !empty($idsArchivos) ? $faker->randomElement($idsArchivos) : null,
                'contacto_tutor'       => $nombreTutor, // <--- CORREGIDO: Máximo 15 chars
                'genero'               => $generoReal, // Aquí SÍ va el género
                'estado_evaluacion'    => 'disponible',
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);

            $this->command->getOutput()->progressAdvance();
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info('✅ Competidores creados exitosamente.');
    }
}
