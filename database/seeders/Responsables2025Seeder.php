<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\Olimpiada;
use App\Model\Rol;
use App\Model\Usuario;
use App\Model\UsuarioRol;
use App\Model\ResponsableArea;
use Illuminate\Support\Facades\Hash;

class Responsables2025Seeder extends Seeder
{
    public function run(): void
    {
        $this->command->info("Creando 3 responsables para gestión 2025...");

        // 1. Obtener olimpiada 2025
        $olimpiada = Olimpiada::where('gestion', '2025')->first();
        if (!$olimpiada) {
            $this->command->warn("Olimpiada 2025 no encontrada.");
            return;
        }

        // 2. Obtener áreas asociadas a la olimpiada
        $areas = $olimpiada->areas()->take(3)->get();
        if ($areas->count() < 3) {
            $this->command->warn("No hay al menos 3 áreas asociadas a la olimpiada 2025.");
            return;
        }

        // 3. Crear rol Responsable si no existe
        $rolResponsable = Rol::firstOrCreate(['nombre' => 'Responsable']);

        // 4. Crear 3 usuarios responsables
        foreach ($areas as $area) {
            $email = strtolower($area->nombre) . '.responsable@olimpiada.com';
            $usuario = Usuario::firstOrCreate(
                ['email' => $email],
                [
                    'id_persona' => null,
                    'password' => Hash::make('responsable123')
                ]
            );

            // Asociar usuario con rol y olimpiada
            UsuarioRol::firstOrCreate([
                'id_usuario' => $usuario->id_usuario,
                'id_rol' => $rolResponsable->id_rol,
                'id_olimpiada' => $olimpiada->id_olimpiada
            ]);

            // Asociar usuario con área_olimpiada
            $areaOlimpiadaId = $area->pivot->id_area_olimpiada;
            ResponsableArea::firstOrCreate([
                'id_usuario' => $usuario->id_usuario,
                'id_area_olimpiada' => $areaOlimpiadaId
            ]);
        }

        $this->command->info("✅ 3 responsables creados con contraseña 'responsable123'.");
    }
}
