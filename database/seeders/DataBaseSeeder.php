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
            // 1. Configuración Base (Sin dependencias)
            RolesSeeder::class,
            OlimpiadaSeeder::class, // Crea la gestión actual
            DepartamentoSeeder::class,
            InstitucionSeeder::class, // (Asegúrate de tener este archivo o crea uno simple si falta)

            // 2. Estructura Académica Base
            AreasSeeder::class,
            NivelesSeeder::class,
            GradoEscolaridadSeeder::class,
            AreaNivelSeeder::class, // Depende de Areas y Niveles

            // 3. Usuarios y Accesos
            UsusariosSeeder::class, // Crea Admin, Responsable, Evaluador base

            // 4. Datos Históricos 
            Olimpiada2021Seeder::class,
            Olimpiada2022Seeder::class,
            Olimpiada2023Seeder::class,
            Olimpiadas2024Seeder::class,

            // 6. Configuración Operativa
            FaseGlobalSeeder::class,
            AccionSistemaSeeder::class,
            ConfiguracionAccionSeeder::class, // Depende de FaseGlobal y AccionSistema

            // 7. Datos de Prueba Específicos (Cuidado con duplicados si ya corriste Olimpiadas2024)
            // AreasEvaluadoresSeeder::class, // Este crea estructura compleja, úsalo si necesitas más datos
            CompetidorSeeder::class,       // Genera competidores masivos para pruebas de carga
            EvaluadorSeeder::class,        // Genera evaluadores extra para pruebas de carga
            ParametroSeeder::class,        // Configura notas mínimas para todos los niveles activos

            Responsables2025Seeder::class,
            AsignarAreasResponsable2Seeder::class,
        ]);
    }
}
