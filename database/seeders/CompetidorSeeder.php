<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\Competidor;
use App\Model\Persona;
use App\Model\Institucion;
use App\Model\AreaNivel;
use App\Model\GradoEscolaridad;
use App\Model\ArchivoCsv;
use Faker\Factory as Faker;

class CompetidorSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Departamentos de Bolivia
        $departamentos = [
            'La Paz', 'Cochabamba', 'Santa Cruz', 'Oruro', 
            'Potosí', 'Tarija', 'Pando'
        ];

        // 1️⃣ Crear instituciones si no existen
        if (Institucion::count() == 0) {
            $institucionesDummy = [
                'Colegio Nacional(Sucre)', 'Unidad Educativa Santa Cruz 2', 
                'Instituto Simón Bolívar', 'Colegio Bolívar "B"', 'Colegio La Paz'
            ];
            foreach ($institucionesDummy as $nombre) {
                Institucion::create(['nombre' => $nombre]);
            }
        }

        $instituciones = Institucion::pluck('id_institucion')->toArray();

        // 2️⃣ Crear archivos CSV dummy si no existen
        if (ArchivoCsv::count() == 0) {
            $olimpiadaId = 1; // Ajusta al id_olimpiada real si tienes más de una
            for ($i = 1; $i <= 5; $i++) {
                ArchivoCsv::create([
                    'nombre' => "Archivo CSV $i",
                    'fecha' => $faker->date(),
                    'id_olimpiada' => $olimpiadaId,
                ]);
            }
        }

        $archivos = ArchivoCsv::pluck('id_archivo_csv')->toArray();

        // 3️⃣ Traer area_nivel y grados existentes
        $areasNiveles = AreaNivel::pluck('id_area_nivel')->toArray();
        $grados = GradoEscolaridad::pluck('id_grado_escolaridad')->toArray();

        if (empty($areasNiveles) || empty($grados)) {
            $this->command->info('Debes tener datos en area_nivel y grado_escolaridad antes de correr este seeder.');
            return;
        }

        // 4️⃣ Crear 100 competidores distribuidos uniformemente
        $numCompetidores = 300;
        for ($i = 1; $i <= $numCompetidores; $i++) {
            $persona = Persona::create([
                'nombre' => $faker->firstName,
                'apellido' => $faker->lastName,
                'ci' => $faker->unique()->numberBetween(1000000, 9999999),
                'genero' => $faker->randomElement(['M', 'F']),
                'telefono' => $faker->unique()->phoneNumber,
                'email' => $faker->unique()->safeEmail,
            ]);

            $departamento = $departamentos[$i % count($departamentos)]; 
            $area_nivel = $areasNiveles[$i % count($areasNiveles)];     
            $grado = $grados[$i % count($grados)];                        

            Competidor::create([
                'departamento' => $departamento,
                'contacto_tutor' => $faker->name,
                'id_grado_escolaridad' => $grado,
                'id_institucion' => $faker->randomElement($instituciones),
                'id_area_nivel' => $area_nivel,
                'id_archivo_csv' => $faker->randomElement($archivos) ?? null,
                'id_persona' => $persona->id_persona,
            ]);
        }

        $this->command->info(' competidores ejecutado correctamente con distribución equilibrada.');
    }
}
