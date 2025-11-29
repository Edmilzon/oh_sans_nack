<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\Usuario;
use App\Model\Persona;
use App\Model\EvaluadorAn;
use App\Model\AreaNivel;
use App\Model\Rol;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class EvaluadorSeeder extends Seeder
{
    public function run(): void
    {
        // ID arbitrario de un area_nivel donde queremos inyectar evaluadores extra
        // Usamos 11 como en tu ejemplo, pero agregamos lógica por si no existe.
        $targetId = 11; 
        $password = 'password123';

        $this->command->info("--- Iniciando EvaluadorSeeder ---");

        // 1. Buscar el AreaNivel (Con sus relaciones para obtener nombres)
        // Nota: En V8, AreaNivel -> AreaOlimpiada -> Area
        $areaNivel = AreaNivel::with(['areaOlimpiada.area', 'areaOlimpiada.olimpiada', 'nivel'])->find($targetId);

        if (!$areaNivel) {
            $this->command->warn("⚠️ No se encontró el AreaNivel con ID {$targetId}. Buscando el primero disponible...");
            $areaNivel = AreaNivel::with(['areaOlimpiada.area', 'areaOlimpiada.olimpiada', 'nivel'])->first();
            
            if (!$areaNivel) {
                $this->command->error("❌ No existen registros en 'area_nivel'. Ejecuta AreasEvaluadoresSeeder primero.");
                return;
            }
        }

        // Obtener datos de contexto
        $nombreArea = $areaNivel->areaOlimpiada->area->nombre_area;
        $nombreNivel = $areaNivel->nivel->nombre_nivel;
        $olimpiada = $areaNivel->areaOlimpiada->olimpiada;

        if (!$olimpiada) {
            $this->command->error("❌ El AreaNivel no tiene una olimpiada asociada válida.");
            return;
        }

        // 2. Obtener el Rol Evaluador
        $rolEvaluador = Rol::where('nombre_rol', 'Evaluador')->first();
        if (!$rolEvaluador) {
            $this->command->error('❌ El rol "Evaluador" no existe en la BD.');
            return;
        }

        $creados = 0;
        
        // 3. Crear 5 Evaluadores Adicionales
        for ($i = 1; $i <= 5; $i++) {
            // Generar datos únicos
            $ci = "999" . str_pad($areaNivel->id_area_nivel, 3, '0', STR_PAD_LEFT) . $i; 
            $email = "eval_extra_{$areaNivel->id_area_nivel}_{$i}@ohsansi.com";

            // Verificar si ya existe la Persona o el Usuario
            $existe = Persona::where('ci_pers', $ci)->exists() || Usuario::where('email_usuario', $email)->exists();

            if ($existe) {
                $this->command->line("⏭️ Evaluador {$i} ya existe ({$email}). Saltando.");
                continue;
            }

            DB::transaction(function () use ($ci, $email, $password, $rolEvaluador, $olimpiada, $areaNivel, $i, &$creados) {
                // A. Crear Persona (Perfil)
                $persona = Persona::create([
                    'nombre_pers' => "Evaluador Extra {$i}",
                    'apellido_pers' => "Area {$areaNivel->id_area_nivel}",
                    'ci_pers' => $ci,
                    'telefono_pers' => '60000000', // Dummy
                    'email_pers' => $email, // Email de contacto
                ]);

                // B. Crear Usuario (Credenciales)
                $usuario = Usuario::create([
                    'id_persona' => $persona->id_persona,
                    'email_usuario' => $email, // Email de login
                    'password_usuario' => Hash::make($password),
                ]);

                // C. Asignar Rol en la Gestión Actual
                $usuario->roles()->attach($rolEvaluador->id_rol, [
                    'id_olimpiada' => $olimpiada->id_olimpiada,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // D. Asignar Permiso Específico para este Area/Nivel
                EvaluadorAn::create([
                    'id_usuario' => $usuario->id_usuario,
                    'id_area_nivel' => $areaNivel->id_area_nivel,
                    'estado_eva_an' => true,
                ]);

                $creados++;
            });
        }

        if ($creados > 0) {
            $this->command->info("✅ Éxito: Se crearon {$creados} evaluadores extra para [{$nombreArea} - {$nombreNivel}].");
            $this->command->info("🔑 Password genérico: {$password}");
        } else {
            $this->command->info("ℹ️ No se crearon nuevos usuarios (ya existían).");
        }
    }
}