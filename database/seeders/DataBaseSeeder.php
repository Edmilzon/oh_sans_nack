<?php

namespace Database\Seeders;

use App\Model\Area;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([

            RolesSeeder::class,
            OlimpiadaSeeder::class,
            DepartamentoSeeder::class,
            InstitucionSeeder::class,
            AreasSeeder::class,
            NivelesSeeder::class,
            GradoEscolaridadSeeder::class,
            AreaNivelSeeder::class,
            UsusariosSeeder::class,
            Olimpiada2021Seeder::class,
            Olimpiada2022Seeder::class,
            Olimpiada2023Seeder::class,
            Olimpiadas2024Seeder::class,
            FaseGlobalSeeder::class,
            AccionSistemaSeeder::class,
            ConfiguracionAccionSeeder::class,
            CompetidorSeeder::class,
            EvaluadorSeeder::class,
            ParametroSeeder::class, 
            Responsables2025Seeder::class,
            AsignarAreasResponsable2Seeder::class,
        ]);
    }
}
