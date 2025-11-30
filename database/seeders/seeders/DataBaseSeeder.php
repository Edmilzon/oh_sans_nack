<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Model\Usuario;
use App\Model\Olimpiada;

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
            AreasSeeder::class,
            NivelesSeeder::class,
            UsusariosSeeder::class,
            Olimpiada2023Seeder::class,
            Olimpiadas2024Seeder::class,
            DepartamentoSeeder::class,
           // TestUserSeeder::class,
           // EvaluadorTestSeeder::class,
            GradoEscolaridadSeeder::class,
            AreasEvaluadoresSeeder::class,
            CompetidorSeeder::class,
            Olimpiada2021Seeder::class,
            FaseGlobalSeeder::class,
            AccionSistemaSeeder::class,
            ConfiguracionAccionSeeder::class,
            EvaluadorSeeder::class,
            ParametroSeeder::class,
            //FasesGestionActualSeeder::class,
        ]);
    }
}