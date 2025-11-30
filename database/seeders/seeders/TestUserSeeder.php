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
use App\Model\AreaOlimpiada;
use App\Model\AreaNivel;
use App\Model\ResponsableArea;
use App\Model\EvaluadorAn;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('Iniciando TestUserSeeder...');

            // --- 1. Crear Usuario de Prueba ---
            $testUser = Usuario::firstOrCreate(
                ['ci' => '111222333444'],
                [
                    'nombre' => 'Carlos',
                    'apellido' => 'Prueba',
                    'email' => 'carlos.prueba@test.com',
                    'password' => bcrypt('password123'),
                    'telefono' => '71234567'
                ]
            );
            $this->command->info("Usuario de prueba '{$testUser->nombre} {$testUser->apellido}' (CI: {$testUser->ci}) creado/encontrado.");

            // --- 2. Obtener o crear entidades necesarias ---
            $olimpiada2024 = Olimpiada::firstOrCreate(['gestion' => '2024'], ['nombre' => 'Olimpiada Científica 2024']);
            $olimpiada2025 = Olimpiada::firstOrCreate(['gestion' => '2025'], ['nombre' => 'Olimpiada Científica 2025']);

            $areaQuimica = Area::firstOrCreate(['nombre' => 'Química']);
            $areaFisica = Area::firstOrCreate(['nombre' => 'Física']);

            $nivel1 = Nivel::firstOrCreate(['nombre' => 'Nivel 1']);
            $nivel2 = Nivel::firstOrCreate(['nombre' => 'Nivel 2']);

            $grado1 = GradoEscolaridad::firstOrCreate(['nombre' => '1ro de Secundaria']);
            $grado2 = GradoEscolaridad::firstOrCreate(['nombre' => '2do de Secundaria']);

            $rolAdmin = Rol::where('nombre', 'Administrador')->first();
            $rolResponsable = Rol::where('nombre', 'Responsable Area')->first();
            $rolEvaluador = Rol::where('nombre', 'Evaluador')->first();

            // --- 3. Crear relaciones para la Olimpiada 2024 ---
            $this->command->info('Asignando roles para la gestión 2024...');

            // Rol: Administrador en 2024
            DB::table('usuario_rol')->insertOrIgnore([
                'id_usuario' => $testUser->id_usuario,
                'id_rol' => $rolAdmin->id_rol,
                'id_olimpiada' => $olimpiada2024->id_olimpiada,
            ]);

            // Rol: Responsable de Química en 2024
            $areaOlimpiadaQuimica2024 = AreaOlimpiada::firstOrCreate([
                'id_area' => $areaQuimica->id_area,
                'id_olimpiada' => $olimpiada2024->id_olimpiada,
            ]);
            DB::table('usuario_rol')->insertOrIgnore([
                'id_usuario' => $testUser->id_usuario,
                'id_rol' => $rolResponsable->id_rol,
                'id_olimpiada' => $olimpiada2024->id_olimpiada,
            ]);
            ResponsableArea::firstOrCreate([
                'id_usuario' => $testUser->id_usuario,
                'id_area_olimpiada' => $areaOlimpiadaQuimica2024->id_area_olimpiada,
            ]);

            // --- 4. Crear relaciones para la Olimpiada 2025 ---
            $this->command->info('Asignando roles para la gestión 2025...');

            // Rol: Responsable de Física en 2025
            $areaOlimpiadaFisica2025 = AreaOlimpiada::firstOrCreate([
                'id_area' => $areaFisica->id_area,
                'id_olimpiada' => $olimpiada2025->id_olimpiada,
            ]);
            DB::table('usuario_rol')->insertOrIgnore([
                'id_usuario' => $testUser->id_usuario,
                'id_rol' => $rolResponsable->id_rol,
                'id_olimpiada' => $olimpiada2025->id_olimpiada,
            ]);
            ResponsableArea::firstOrCreate([
                'id_usuario' => $testUser->id_usuario,
                'id_area_olimpiada' => $areaOlimpiadaFisica2025->id_area_olimpiada,
            ]);

            // Rol: Evaluador de Física, Nivel 2, 2do de Secundaria en 2025
            $areaNivelFisica2025 = AreaNivel::firstOrCreate([
                'id_area' => $areaFisica->id_area, 'id_nivel' => $nivel2->id_nivel, 'id_grado_escolaridad' => $grado2->id_grado_escolaridad, 'id_olimpiada' => $olimpiada2025->id_olimpiada
            ]);
            DB::table('usuario_rol')->insertOrIgnore(['id_usuario' => $testUser->id_usuario, 'id_rol' => $rolEvaluador->id_rol, 'id_olimpiada' => $olimpiada2025->id_olimpiada]);
            EvaluadorAn::firstOrCreate(['id_usuario' => $testUser->id_usuario, 'id_area_nivel' => $areaNivelFisica2025->id_area_nivel]);

            $this->command->info('TestUserSeeder completado exitosamente!');
        });
    }
}