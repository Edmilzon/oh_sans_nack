<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\Parametro;
use App\Model\AreaNivel;

class ParametroSeeder extends Seeder
{
    public function run(): void
    {
        $idAreaNivel = 11;

        $areaNivel = AreaNivel::find($idAreaNivel);

        if ($areaNivel) {
            Parametro::firstOrCreate(
                ['id_area_nivel' => $areaNivel->id_area_nivel],
                [
                    'nota_min_clasif' => 51.0,
                    'cantidad_max_apro' => 100,
                ]
            );
            
            $areaNivel->load('area', 'nivel');
            $this->command->info("Parámetro de calificación creado o verificado para {$areaNivel->area->nombre} - {$areaNivel->nivel->nombre}.");
        } else {
            $this->command->warn("No se encontró el AreaNivel con ID {$idAreaNivel}. No se creó el parámetro.");
        }
    }
}
