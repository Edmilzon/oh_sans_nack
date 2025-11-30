<?php

namespace Database\Seeders;

use App\Model\Olimpiada;
use App\Model\Usuario;
use App\Model\Persona;
use App\Model\Rol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsusariosSeeder extends Seeder{
    
    public function run():void{
        // Buscar la olimpiada del año actual, que fue creada por OlimpiadaSeeder.
        $olimpiada = Olimpiada::where('gestion_olimp', date('Y'))->first();

        if (!$olimpiada) {
            $this->command->error('No se encontró la olimpiada para el año actual. Asegúrate de que OlimpiadaSeeder se ejecute primero.');
            return;
        }

        // Crear usuarios de prueba para cada rol
        $usuarios = [
            [
                'nombre_pers' => 'Admin',
                'apellido_pers' => 'Sistema',
                'ci_pers' => '12345678',
                'email_pers' => 'admin@ohsansi.com',
                'password_usuario' => Hash::make('admin123'),
                'telefono_pers' => '12345678',
                'rol' => 'Administrador'
            ],
            [
                'nombre_pers' => 'Juan',
                'apellido_pers' => 'Responsable',
                'ci_pers' => '87654321',
                'email_pers' => 'responsable@ohsansi.com',
                'password_usuario' => Hash::make('responsable123'),
                'telefono_pers' => '87654321',
                'rol' => 'Responsable Area'
            ],
            [
                'nombre_pers' => 'María',
                'apellido_pers' => 'Evaluadora',
                'ci_pers' => '11223344',
                'email_pers' => 'evaluador@ohsansi.com',
                'password_usuario' => Hash::make('evaluador123'),
                'telefono_pers' => '11223344',
                'rol' => 'Evaluador'
            ],
        ];

        foreach ($usuarios as $usuarioData) {
            // 1. Crear la Persona
            $persona = Persona::create([
                'nombre_pers' => $usuarioData['nombre_pers'],
                'apellido_pers' => $usuarioData['apellido_pers'],
                'ci_pers' => $usuarioData['ci_pers'],
                'telefono_pers' => $usuarioData['telefono_pers'],
                'email_pers' => $usuarioData['email_pers'],
            ]);

            // 2. Crear el Usuario
            $usuario = Usuario::create([
                'id_persona' => $persona->id_persona,
                'email_usuario' => $usuarioData['email_pers'],
                'password_usuario' => $usuarioData['password_usuario'],
            ]);
            
            // 3. Asignar Rol
            $usuario->asignarRol($usuarioData['rol'], $olimpiada->id_olimpiada);
        }

        $this->command->info('Usuarios de prueba creados exitosamente:');
        $this->command->info('- Admin: admin@ohsansi.com, Password: admin123');
        $this->command->info('- Responsable: responsable@ohsansi.com, Password: responsable123');
        $this->command->info('- Evaluador: evaluador@ohsansi.com, Password: evaluador123');
    }
}