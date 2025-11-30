<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Model\Olimpiada;
use App\Model\FaseGlobal;
use App\Model\AccionSistema;

class ConfiguracionAccionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $olimpiada = Olimpiada::where('gestion', '2025')->first();
        $fases = FaseGlobal::all();
        $acciones = AccionSistema::all();

        if (!$olimpiada) {
            $this->command->error('No se encontró la olimpiada para la gestión 2025. Ejecute Olimpiada2025Seeder primero.');
            return;
        }

        // Datos de ejemplo basados en la API
        $configuraciones = [
            // REG_ESTUD
            ['id_fase_global' => 1, 'id_accion' => 10, 'habilitada' => true],
            ['id_fase_global' => 2, 'id_accion' => 10, 'habilitada' => false],
            ['id_fase_global' => 3, 'id_accion' => 10, 'habilitada' => false],
            // CARGAR_NOTAS
            ['id_fase_global' => 1, 'id_accion' => 20, 'habilitada' => false],
            ['id_fase_global' => 2, 'id_accion' => 20, 'habilitada' => true],
            ['id_fase_global' => 3, 'id_accion' => 20, 'habilitada' => false],
            // PUB_CLASIF (por defecto deshabilitado en todas las fases)
            ['id_fase_global' => 1, 'id_accion' => 30, 'habilitada' => false],
            ['id_fase_global' => 2, 'id_accion' => 30, 'habilitada' => false],
            ['id_fase_global' => 3, 'id_accion' => 30, 'habilitada' => false],
        ];

        foreach ($configuraciones as $config) {
            DB::table('configuracion_accion')->insert([
                'id_olimpiada' => $olimpiada->id_olimpiada,
                'id_fase_global' => $config['id_fase_global'],
                'id_accion' => $config['id_accion'],
                'habilitada' => $config['habilitada'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
