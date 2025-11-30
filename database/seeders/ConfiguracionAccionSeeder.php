<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Model\Olimpiada;
use App\Model\FaseGlobal;
use App\Model\AccionSistema;

class ConfiguracionAccionSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // 1. Obtener la Olimpiada Actual
        $olimpiada = Olimpiada::where('gestion_olimp', date('Y'))->first();
        if (!$olimpiada) {
            $olimpiada = Olimpiada::first();
            if (!$olimpiada) {
                $this->command->error('❌ No hay olimpiadas registradas. Ejecuta OlimpiadaSeeder primero.');
                return;
            }
        }

        $this->command->info("⚙️ Configurando acciones para la olimpiada: {$olimpiada->nombre_olimp}");

        // 2. Obtener Fases Globales (Asumimos que existen por el FaseGlobalSeeder)
        // Nota: Ajusta los códigos ('F1_INS', 'F2_DIS', etc.) según lo que tengas en tu FaseGlobalSeeder
        // Si no usas códigos, puedes buscar por nombre. Aquí busco por orden para ser genérico.
        
        $faseInscripcion = FaseGlobal::where('orden_fas_glo', 1)->first(); // Fase 1: Inscripciones
        $faseDistrital   = FaseGlobal::where('orden_fas_glo', 2)->first(); // Fase 2: Exámenes/Evaluación
        $faseFinal       = FaseGlobal::where('orden_fas_glo', 3)->first(); // Fase 3: Final

        if (!$faseInscripcion || !$faseDistrital) {
            $this->command->warn('⚠️ Faltan fases globales. Se intentará configurar con lo que haya.');
        }

        // 3. Obtener Acciones del Sistema (Por código para ser exactos)
        $acciones = AccionSistema::all()->keyBy('codigo_acc_sis');

        $configuraciones = [];

        // --- CONFIGURACIÓN FASE 1: INSCRIPCIÓN ---
        if ($faseInscripcion) {
            // En esta fase SÍ se puede inscribir, pero NO evaluar
            $accionesF1 = ['INSCRIP_EST', 'IMP_CSV_EST', 'CREAR_USUARIO', 'ASIGNAR_ROL', 'CONF_CRONOGRAMA'];
            
            foreach ($accionesF1 as $codigo) {
                if (isset($acciones[$codigo])) {
                    $configuraciones[] = [
                        'id_olimpiada'   => $olimpiada->id_olimpiada,
                        'id_fase_global' => $faseInscripcion->id_fase_global,
                        'id_accion'      => $acciones[$codigo]->id_accion,
                        'habilitada'     => true, // <--- HABILITADO
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ];
                }
            }
            
            // Las acciones de evaluación (REG_NOTA) existen pero están DESHABILITADAS (false) o no se crean
            // (Si no está en la tabla, se asume false por defecto, pero podemos ser explícitos)
        }

        // --- CONFIGURACIÓN FASE 2: DISTRITAL (EVALUACIÓN) ---
        if ($faseDistrital) {
            // En esta fase YA NO se inscribe, pero SÍ se evalúa
            $accionesF2 = ['REG_NOTA', 'VER_REP_NOTAS', 'CREAR_COMP', 'CONF_CRONOGRAMA'];

            foreach ($accionesF2 as $codigo) {
                if (isset($acciones[$codigo])) {
                    $configuraciones[] = [
                        'id_olimpiada'   => $olimpiada->id_olimpiada,
                        'id_fase_global' => $faseDistrital->id_fase_global,
                        'id_accion'      => $acciones[$codigo]->id_accion,
                        'habilitada'     => true,
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ];
                }
            }
        }

        // --- CONFIGURACIÓN FASE 3: FINAL ---
        if ($faseFinal) {
            // Solo reportes y configuración
            $accionesF3 = ['VER_REP_NOTAS', 'CONF_CRONOGRAMA'];

            foreach ($accionesF3 as $codigo) {
                if (isset($acciones[$codigo])) {
                    $configuraciones[] = [
                        'id_olimpiada'   => $olimpiada->id_olimpiada,
                        'id_fase_global' => $faseFinal->id_fase_global,
                        'id_accion'      => $acciones[$codigo]->id_accion,
                        'habilitada'     => true,
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ];
                }
            }
        }

        // 4. Insertar masivamente
        if (!empty($configuraciones)) {
            // Usamos upsert para evitar errores de duplicados si se corre varias veces
            DB::table('configuracion_accion')->upsert(
                $configuraciones, 
                ['id_olimpiada', 'id_fase_global', 'id_accion'], // Clave única compuesta
                ['habilitada', 'updated_at'] // Qué actualizar si existe
            );
            $this->command->info('✅ Matriz de permisos (ConfiguraciónAccion) generada exitosamente.');
        } else {
            $this->command->warn('⚠️ No se generaron configuraciones (Faltan datos previos).');
        }
    }
}