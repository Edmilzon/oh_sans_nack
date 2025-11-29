<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. Catálogos Base
            RolesSeeder::class,
            NivelesSeeder::class,
            GradoEscolaridadSeeder::class,
            DepartamentoSeeder::class,
            FaseGlobalSeeder::class,
            AccionSistemaSeeder::class,
            RolAccionSeeder::class, // <--- NUEVO (Define permisos base)

            // 2. Estructura Académica Base (Gestión Actual)
            OlimpiadaSeeder::class,
            AreasSeeder::class,

            // 3. Usuarios y Personal Base
            UsuariosSeeder::class,

            // 4. Configuración de Áreas (Crea area_nivel)
            AreasEvaluadoresSeeder::class,
            
            // 5. Configuración Dependiente de area_nivel
            NivelGradoSeeder::class,         // <--- NUEVO (Vincula grados a las áreas creadas)
            ParametroSeeder::class,
            ParametroMedalleroSeeder::class, // <--- NUEVO (Define medallas)
            ConfiguracionAccionSeeder::class, // Define permisos por fase

            // 6. Datos Históricos
            Olimpiada2021Seeder::class,
            Olimpiada2023Seeder::class,
            Olimpiadas2024Seeder::class,

            // 7. Gestión Actual (Operativa)
            CompetidorSeeder::class,
            EvaluadorSeeder::class,
            
            // 8. Inicialización del Sistema
            FasesGestionActualSeeder::class,
        ]);
    }
}