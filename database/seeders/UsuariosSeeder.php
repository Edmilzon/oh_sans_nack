<?php

namespace Database\Seeders;

use App\Model\Olimpiada;
use App\Model\Usuario;
use App\Model\Persona;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuariosSeeder extends Seeder // <--- Nombre corregido
{
    public function run(): void
    {
        // Corrección V8: 'gestion_olimp' en lugar de 'gestion'
        $olimpiada = Olimpiada::where('gestion_olimp', date('Y'))->first();

        if (!$olimpiada) {
            // Fallback: intenta crearla si no existe, o busca la primera
            $olimpiada = Olimpiada::first();
            if(!$olimpiada) {
                $this->command->error('❌ No hay olimpiadas. Ejecuta OlimpiadaSeeder primero.');
                return;
            }
        }

        $usuarios = [
            [
                'role' => 'Administrador',
                'persona' => [
                    'nombre_pers' => 'Admin', 'apellido_pers' => 'Sistema',
                    'ci_pers' => '12345678', 'telefono_pers' => '12345678', 'email_pers' => 'admin@ohsansi.com'
                ],
                'auth' => ['email_usuario' => 'admin@ohsansi.com', 'password_usuario' => Hash::make('admin123')]
            ],
            [
                'role' => 'Responsable Area',
                'persona' => [
                    'nombre_pers' => 'Juan', 'apellido_pers' => 'Responsable',
                    'ci_pers' => '87654321', 'telefono_pers' => '87654321', 'email_pers' => 'responsable@ohsansi.com'
                ],
                'auth' => ['email_usuario' => 'responsable@ohsansi.com', 'password_usuario' => Hash::make('responsable123')]
            ],
            [
                'role' => 'Evaluador',
                'persona' => [
                    'nombre_pers' => 'María', 'apellido_pers' => 'Evaluadora',
                    'ci_pers' => '11223344', 'telefono_pers' => '11223344', 'email_pers' => 'evaluador@ohsansi.com'
                ],
                'auth' => ['email_usuario' => 'evaluador@ohsansi.com', 'password_usuario' => Hash::make('evaluador123')]
            ],
        ];

        foreach ($usuarios as $data) {
            // 1. Crear Persona (V8)
            $persona = Persona::create($data['persona']);

            // 2. Crear Usuario vinculado
            $usuario = Usuario::create([
                'id_persona' => $persona->id_persona,
                ...$data['auth']
            ]);

            // 3. Asignar Rol en la gestión actual
            $usuario->asignarRol($data['role'], $olimpiada->id_olimpiada);
        }

        $this->command->info('✅ Usuarios V8 creados (Admin, Responsable, Evaluador).');
    }
}
