<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\Olimpiada;
use App\Model\Usuario;
use App\Model\Persona;
use App\Model\Rol;

class UsusariosSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buscar la olimpiada de la gestión actual
        // Prioriza la del año actual (creada por OlimpiadaSeeder)
        $olimpiada = Olimpiada::where('gestion', date('Y'))->first();

        if (!$olimpiada) {
            $this->command->error('❌ No se encontró una olimpiada para el año actual (' . date('Y') . '). Ejecuta OlimpiadaSeeder primero.');
            return;
        }

        // 2. Datos de prueba estructurados (Persona + Usuario + Rol)
        $usuariosData = [
            [
                'rol_nombre' => 'Administrador',
                'persona' => [
                    'nombre' => 'Admin',
                    'apellido' => 'Sistema',
                    'ci' => '12345678',
                    'telefono' => '70000001',
                    'email' => 'admin.persona@test.com',
                ],
                'usuario' => [
                    'email' => 'admin@ohsansi.com', // Email de acceso
                    'password' => 'admin123' // Texto plano (el modelo lo encripta)
                ]
            ],
            [
                'rol_nombre' => 'Responsable Area',
                'persona' => [
                    'nombre' => 'Juan',
                    'apellido' => 'Responsable',
                    'ci' => '87654321',
                    'telefono' => '70000002',
                    'email' => 'juan.persona@test.com',
                ],
                'usuario' => [
                    'email' => 'responsable@ohsansi.com',
                    'password' => 'responsable123'
                ]
            ],
            [
                'rol_nombre' => 'Evaluador',
                'persona' => [
                    'nombre' => 'María',
                    'apellido' => 'Evaluadora',
                    'ci' => '11223344',
                    'telefono' => '70000003',
                    'email' => 'maria.persona@test.com',
                ],
                'usuario' => [
                    'email' => 'evaluador@ohsansi.com',
                    'password' => 'evaluador123'
                ]
            ],
        ];

        $this->command->info('Creando usuarios y asignando roles...');

        foreach ($usuariosData as $data) {
            // A. Crear o Buscar Persona (Evita duplicados por CI)
            $persona = Persona::firstOrCreate(
                ['ci' => $data['persona']['ci']],
                $data['persona']
            );

            // B. Crear o Buscar Usuario vinculado
            $usuario = Usuario::firstOrCreate(
                ['email' => $data['usuario']['email']],
                [
                    'id_persona' => $persona->id_persona,
                    'password'   => $data['usuario']['password'] // El modelo aplica el Hash automáticamente
                ]
            );

            // C. Asignar Rol en la tabla pivote (usuario_rol)
            $rol = Rol::where('nombre', $data['rol_nombre'])->first();

            if ($rol) {
                // Verificamos si ya tiene el rol en esta olimpiada para no duplicar
                $yaTieneRol = $usuario->roles()
                                      ->where('rol.id_rol', $rol->id_rol)
                                      ->wherePivot('id_olimpiada', $olimpiada->id_olimpiada)
                                      ->exists();

                if (!$yaTieneRol) {
                    $usuario->roles()->attach($rol->id_rol, [
                        'id_olimpiada' => $olimpiada->id_olimpiada
                    ]);
                }
            }
        }

        $this->command->info('✅ Usuarios de prueba creados exitosamente:');
        $this->command->info('   - Admin: admin@ohsansi.com / admin123');
        $this->command->info('   - Responsable: responsable@ohsansi.com / responsable123');
        $this->command->info('   - Evaluador: evaluador@ohsansi.com / evaluador123');
    }
}
