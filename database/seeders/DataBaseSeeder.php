<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. CATÁLOGOS (Lo primero siempre)
            RolesSeeder::class,
            NivelesSeeder::class,
            GradoEscolaridadSeeder::class,
            DepartamentoSeeder::class,
            FaseGlobalSeeder::class,
            AccionSistemaSeeder::class,
            RolAccionSeeder::class,

            // 2. ESTRUCTURA BASE
            OlimpiadaSeeder::class,
            AreasSeeder::class,
            AreaOlimpiadaSeeder::class,
            AreaNivelSeeder::class,
            UsuariosSeeder::class,
            AreasEvaluadoresSeeder::class,

            // 3. CONFIGURACIÓN
            NivelGradoSeeder::class,
            ParametroSeeder::class,
            ParametroMedalleroSeeder::class,
            ConfiguracionAccionSeeder::class,

            // 4. DATOS HISTÓRICOS (Ahora sí funcionarán porque ya existen los grados)
            Olimpiada2021Seeder::class,
            Olimpiada2022Seeder::class,    // Si creaste este archivo
            Olimpiada2023Seeder::class,
            Olimpiadas2024Seeder::class,

            // 5. OPERATIVIDAD ACTUAL
            CompetidorSeeder::class,
            EvaluadorSeeder::class,
            // EvaluadorTestSeeder::class,

            // 6. ARRANQUE FINAL
            FasesGestionActualSeeder::class,
        ]);
    }
}
