<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Model\ResponsableArea;

class AsignarAreasResponsable2Seeder extends Seeder
{
    public function run(): void
    {
        $this->command->info("Asignando 2 áreas al usuario responsable 2...");

        $idUsuario = 2;

        // IDs de área_olimpiada que deseas asignar
        $areas = [4,5]; // <--- CAMBIA ESTOS 3 A TUS IDs REALES

        foreach ($areas as $idAreaOlimpiada) {

            // Verificar si existe esa área
            $area = DB::table('area_olimpiada')
                ->where('id_area_olimpiada', $idAreaOlimpiada)
                ->first();

            if (!$area) {
                $this->command->warn("⚠ área_olimpiada {$idAreaOlimpiada} no existe, se omite.");
                continue;
            }

            // Crear asignación
            ResponsableArea::firstOrCreate([
                'id_usuario'        => $idUsuario,
                'id_area_olimpiada' => $idAreaOlimpiada,
            ]);
        }

        $this->command->info("✔ 2 áreas asignadas al usuario responsable 2.");
    }
}
