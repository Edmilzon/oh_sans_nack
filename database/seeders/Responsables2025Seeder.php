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

        $olimpiada = Olimpiada::where('gestion', '2025')->first();
        if (!$olimpiada) {
            $this->command->warn("⚠ No existe la olimpiada 2025.");
            return;
        }

        $responsables = [
            'Matemáticas' => 'Math2025!',
            'Física'      => 'Fys2025#',
            'Química'     => 'Chem2025$'
        ];

        $rolResponsable = Rol::firstOrCreate(['nombre' => 'Responsable']);

        foreach ($responsables as $nombreArea => $password) {

            $area = DB::table('area')->where('nombre', $nombreArea)->first();
            if (!$area) {
                $this->command->warn("⚠ Área '{$nombreArea}' no existe.");
                continue;
            }

            $areaOlimpiada = DB::table('area_olimpiada')
                ->where('id_area', $area->id_area)
                ->where('id_olimpiada', $olimpiada->id_olimpiada)
                ->first();

            if (!$areaOlimpiada) {
                $this->command->warn("⚠ No existe area_olimpiada para {$nombreArea} en 2025.");
                continue;
            }

            $slugArea = strtolower($nombreArea);
            $slugArea = iconv('UTF-8', 'ASCII//TRANSLIT', $slugArea);
            $slugArea = preg_replace('/[^a-z0-9]/', '', $slugArea);

            $email = $slugArea . '.responsable@olimpiada.com';

            $persona = Persona::firstOrCreate(
                ['ci' => rand(1000000, 9999999)],
                [
                    'nombre'   => "{$nombreArea} Responsable",
                    'apellido' => "2025",
                    'telefono' => '60000000',
                    'email'    => $email
                ]
            );

            $usuario = Usuario::firstOrCreate(
                ['email' => $email],
                [
                    'id_persona' => $persona->id_persona,
                    'password'   => Hash::make($password)
                ]
            );

            UsuarioRol::firstOrCreate([
                'id_usuario'   => $usuario->id_usuario,
                'id_rol'       => $rolResponsable->id_rol,
                'id_olimpiada' => $olimpiada->id_olimpiada
            ]);

            ResponsableArea::firstOrCreate([
                'id_usuario'        => $usuario->id_usuario,
                'id_area_olimpiada' => $areaOlimpiada->id_area_olimpiada
            ]);

            $this->command->info("✔ Responsable creado para área: {$nombreArea}");
        }

        $this->command->info("🎉 Responsables creados y asociados correctamente.");
    }
}
