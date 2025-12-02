<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\Olimpiada;
use App\Model\Rol;
use App\Model\Usuario;
use App\Model\UsuarioRol;
use App\Model\ResponsableArea;
use App\Model\Persona;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class Responsables2025Seeder extends Seeder
{
    public function run(): void
    {
        $this->command->info("Creando responsables con contraseñas diferentes para 2025...");

        // 1. Buscar olimpiada 2025
        $olimpiada = Olimpiada::where('gestion', '2025')->first();
        if (!$olimpiada) {
            $this->command->warn("⚠ No existe la olimpiada 2025.");
            return;
        }

        // Áreas y contraseñas
        $responsables = [
            'Matemáticas' => 'Math2025!',
            'Física'      => 'Fys2025#',
            'Química'     => 'Chem2025$'
        ];

        // Crear rol Responsable si no existe
        $rolResponsable = Rol::firstOrCreate(['nombre' => 'Responsable']);

        foreach ($responsables as $nombreArea => $password) {

            // 2. Buscar área por nombre
            $area = DB::table('area')->where('nombre', $nombreArea)->first();
            if (!$area) {
                $this->command->warn("⚠ Área '{$nombreArea}' no existe.");
                continue;
            }

            // 3. Buscar la relación area_olimpiada
            $areaOlimpiada = DB::table('area_olimpiada')
                ->where('id_area', $area->id_area)
                ->where('id_olimpiada', $olimpiada->id_olimpiada)
                ->first();

            if (!$areaOlimpiada) {
                $this->command->warn("⚠ No existe area_olimpiada para {$nombreArea} en 2025.");
                continue;
            }

            // 4. Crear email normalizado: minúscula, sin acentos, sin caracteres raros
            $slugArea = strtolower($nombreArea);
            $slugArea = iconv('UTF-8', 'ASCII//TRANSLIT', $slugArea);  // quitar acentos
            $slugArea = preg_replace('/[^a-z0-9]/', '', $slugArea);    // limpiar

            $email = $slugArea . '.responsable@olimpiada.com';

            // 5. Crear persona
            $persona = Persona::firstOrCreate(
                ['ci' => rand(1000000, 9999999)],
                [
                    'nombre'   => "{$nombreArea} Responsable",
                    'apellido' => "2025",
                    'telefono' => '60000000',
                    'email'    => $email
                ]
            );

            // 6. Crear usuario
            $usuario = Usuario::firstOrCreate(
                ['email' => $email],
                [
                    'id_persona' => $persona->id_persona,
                    'password'   => Hash::make($password)
                ]
            );

            // 7. Asociar usuario al rol y olimpiada
            UsuarioRol::firstOrCreate([
                'id_usuario'   => $usuario->id_usuario,
                'id_rol'       => $rolResponsable->id_rol,
                'id_olimpiada' => $olimpiada->id_olimpiada
            ]);

            // 8. Asociar usuario a área en la olimpiada
            ResponsableArea::firstOrCreate([
                'id_usuario'        => $usuario->id_usuario,
                'id_area_olimpiada' => $areaOlimpiada->id_area_olimpiada
            ]);

            $this->command->info("✔ Responsable creado para área: {$nombreArea}");
        }

        $this->command->info("🎉 Responsables creados y asociados correctamente.");
    }
}
