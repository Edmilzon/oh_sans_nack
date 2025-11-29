<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Model\Usuario;
use App\Model\Persona;
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
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('🚀 Iniciando TestUserSeeder V8...');

            // --- 1. Crear Usuario de Prueba (Persona + Usuario) ---
            
            // Paso A: Persona
            $persona = Persona::firstOrCreate(
                ['ci_pers' => '111222333444'],
                [
                    'nombre_pers' => 'Carlos',
                    'apellido_pers' => 'Prueba',
                    'email_pers' => 'carlos.prueba@test.com',
                    'telefono_pers' => '71234567'
                ]
            );

            // Paso B: Usuario
            $testUser = Usuario::firstOrCreate(
                ['email_usuario' => 'carlos.prueba@test.com'],
                [
                    'id_persona' => $persona->id_persona,
                    'password_usuario' => Hash::make('password123'),
                ]
            );
            
            $this->command->info("Usuario de prueba '{$persona->nombre_pers}' creado.");

            // --- 2. Obtener o crear entidades necesarias ---
            
            $olimpiada2024 = Olimpiada::firstOrCreate(['gestion_olimp' => '2024'], ['nombre_olimp' => 'Olimpiada Científica 2024', 'estado_olimp' => false]);
            $olimpiada2025 = Olimpiada::firstOrCreate(['gestion_olimp' => '2025'], ['nombre_olimp' => 'Olimpiada Científica 2025', 'estado_olimp' => true]);

            $areaQuimica = Area::firstOrCreate(['nombre_area' => 'Química']);
            $areaFisica = Area::firstOrCreate(['nombre_area' => 'Física']);

            $nivel1 = Nivel::firstOrCreate(['nombre_nivel' => 'Primaria']); // Ajustado a V8
            $nivel2 = Nivel::firstOrCreate(['nombre_nivel' => 'Secundaria']);

            $rolAdmin = Rol::where('nombre_rol', 'Administrador')->first();
            $rolResponsable = Rol::where('nombre_rol', 'Responsable Area')->first();
            $rolEvaluador = Rol::where('nombre_rol', 'Evaluador')->first();

            if (!$rolAdmin || !$rolResponsable || !$rolEvaluador) {
                $this->command->error('Faltan roles. Ejecuta RolesSeeder primero.');
                return;
            }

            // --- 3. Crear relaciones para la Olimpiada 2024 (Pasada) ---
            $this->command->info('Asignando roles para la gestión 2024...');

            // Rol: Administrador en 2024
            // Usamos syncWithoutDetaching para no duplicar
            $testUser->roles()->syncWithoutDetaching([
                $rolAdmin->id_rol => ['id_olimpiada' => $olimpiada2024->id_olimpiada]
            ]);

            // Rol: Responsable de Química en 2024
            // A. Crear AreaOlimpiada
            $aoQuimica24 = AreaOlimpiada::firstOrCreate([
                'id_area' => $areaQuimica->id_area,
                'id_olimpiada' => $olimpiada2024->id_olimpiada,
            ]);
            
            // B. Asignar Rol Responsable
            $testUser->roles()->syncWithoutDetaching([
                $rolResponsable->id_rol => ['id_olimpiada' => $olimpiada2024->id_olimpiada]
            ]);

            // C. Asignar Responsabilidad Específica
            ResponsableArea::firstOrCreate([
                'id_usuario' => $testUser->id_usuario,
                'id_area_olimpiada' => $aoQuimica24->id_area_olimpiada,
            ]);

            // --- 4. Crear relaciones para la Olimpiada 2025 (Actual) ---
            $this->command->info('Asignando roles para la gestión 2025...');

            // Rol: Responsable de Física en 2025
            $aoFisica25 = AreaOlimpiada::firstOrCreate([
                'id_area' => $areaFisica->id_area,
                'id_olimpiada' => $olimpiada2025->id_olimpiada,
            ]);
            
            $testUser->roles()->syncWithoutDetaching([
                $rolResponsable->id_rol => ['id_olimpiada' => $olimpiada2025->id_olimpiada]
            ]);
            
            ResponsableArea::firstOrCreate([
                'id_usuario' => $testUser->id_usuario,
                'id_area_olimpiada' => $aoFisica25->id_area_olimpiada,
            ]);

            // Rol: Evaluador de Física - Secundaria en 2025
            // A. Crear AreaNivel
            $anFisicaSec25 = AreaNivel::firstOrCreate([
                'id_area_olimpiada' => $aoFisica25->id_area_olimpiada,
                'id_nivel' => $nivel2->id_nivel, // Secundaria
            ], ['es_activo_area_nivel' => true]);

            // B. Asignar Rol Evaluador
            $testUser->roles()->syncWithoutDetaching([
                $rolEvaluador->id_rol => ['id_olimpiada' => $olimpiada2025->id_olimpiada]
            ]);

            // C. Asignar Permiso de Evaluación
            EvaluadorAn::firstOrCreate([
                'id_usuario' => $testUser->id_usuario,
                'id_area_nivel' => $anFisicaSec25->id_area_nivel
            ], ['estado_eva_an' => true]);

            $this->command->info('✅ TestUserSeeder completado exitosamente!');
            $this->command->info("Usuario Multi-Rol configurado: Admin (2024), Resp. Química (2024), Resp. Física (2025), Eval. Física (2025).");
        });
    }
}