<?php

namespace Database\Seeders;

use App\Model\Olimpiada;
use App\Model\Usuario;
use App\Model\Persona;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuariosSeeder extends Seeder {
    
    public function run():void{
        $olimpiada = Olimpiada::where('gestion_olimp', date('Y'))->first();

        if (!$olimpiada) {
            // Fallback si no encuentra la gestión actual, usar la primera disponible
            $olimpiada = Olimpiada::first();
        }

        if (!$olimpiada) {
             $this->command->error('No hay olimpiadas creadas.');
             return;
        }

        $usuariosData = [
            [
                'role' => 'Administrador',
                'persona' => [
                    'nombre_pers' => 'Admin', 'apellido_pers' => 'Sistema', 
                    'ci_pers' => '12345678', 'telefono_pers' => '12345678', 'email_pers' => 'admin@ohsansi.com'
                ],
                'usuario' => ['email_usuario' => 'admin@ohsansi.com', 'password_usuario' => Hash::make('admin123')]
            ],
            [
                'role' => 'Responsable Area',
                'persona' => [
                    'nombre_pers' => 'Juan', 'apellido_pers' => 'Responsable', 
                    'ci_pers' => '87654321', 'telefono_pers' => '87654321', 'email_pers' => 'responsable@ohsansi.com'
                ],
                'usuario' => ['email_usuario' => 'responsable@ohsansi.com', 'password_usuario' => Hash::make('responsable123')]
            ],
            [
                'role' => 'Evaluador',
                'persona' => [
                    'nombre_pers' => 'María', 'apellido_pers' => 'Evaluadora', 
                    'ci_pers' => '11223344', 'telefono_pers' => '11223344', 'email_pers' => 'evaluador@ohsansi.com'
                ],
                'usuario' => ['email_usuario' => 'evaluador@ohsansi.com', 'password_usuario' => Hash::make('evaluador123')]
            ],
        ];

        foreach ($usuariosData as $data) {
            // 1. Crear Persona
            $persona = Persona::create($data['persona']);
            
            // 2. Crear Usuario vinculado
            $usuario = Usuario::create([
                'id_persona' => $persona->id_persona,
                ...$data['usuario']
            ]);
            
            // 3. Asignar Rol
            $usuario->asignarRol($data['role'], $olimpiada->id_olimpiada);
        }

        $this->command->info('Usuarios de prueba creados exitosamente.');
    }
}