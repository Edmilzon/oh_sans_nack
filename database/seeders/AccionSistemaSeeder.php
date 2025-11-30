<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccionSistemaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $acciones = [
            // Gestión de Usuarios y Roles
            [
                'codigo_acc_sis' => 'MOD_CREAR_USUARIO',
                'nombre_acc_sis' => 'Crear Usuario',
                'descripcion_acc_sis' => 'Permite registrar nuevos usuarios en el sistema.'
            ],
            [
                'codigo_acc_sis' => 'MOD_ASIGNAR_ROL',
                'nombre_acc_sis' => 'Asignar Rol',
                'descripcion_acc_sis' => 'Permite asignar roles a un usuario existente.'
            ],
            
            // Gestión de Inscripciones (Clave para Responsables de Sede)
            [
                'codigo_acc_sis' => 'MOD_INSCRIP_EST',
                'nombre_acc_sis' => 'Inscribir Estudiante',
                'descripcion_acc_sis' => 'Permite registrar un competidor en un área/nivel.'
            ],
            [
                'codigo_acc_sis' => 'MOD_IMP_CSV_EST',
                'nombre_acc_sis' => 'Importar Estudiantes CSV',
                'descripcion_acc_sis' => 'Carga masiva de estudiantes desde archivo.'
            ],

            // Gestión de Competencias y Exámenes
            [
                'codigo_acc_sis' => 'MOD_CREAR_COMP',
                'nombre_acc_sis' => 'Crear Competencia',
                'descripcion_acc_sis' => 'Permite programar un nuevo examen.'
            ],
            
            // Evaluación (Clave para Evaluadores)
            [
                'codigo_acc_sis' => 'MOD_REG_NOTA',
                'nombre_acc_sis' => 'Registrar Nota',
                'descripcion_acc_sis' => 'Permite al evaluador ingresar la calificación de un estudiante.'
            ],
            [
                'codigo_acc_sis' => 'MOD_VER_REP_NOTAS',
                'nombre_acc_sis' => 'Ver Reporte Notas',
                'descripcion_acc_sis' => 'Permite visualizar el listado de calificaciones.'
            ],
            
            // Gestión del Sistema
            [
                'codigo_acc_sis' => 'MOD_CONF_CRONOGRAMA',
                'nombre_acc_sis' => 'Configurar Cronograma',
                'descripcion_acc_sis' => 'Permite definir fechas de inicio y fin de fases.'
            ],
        ];

        // Usamos insertOrIgnore para evitar errores si se corre el seeder dos veces
        DB::table('accion_sistema')->insertOrIgnore($acciones);
        
        $this->command->info('Acciones del sistema insertadas correctamente.');
    }
}