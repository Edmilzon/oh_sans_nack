<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\Olimpiada;
use App\Model\FaseGlobal;
use App\Model\AccionSistema;
use App\Model\ConfiguracionAccion;

class ConfiguracionAccionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $olimpiada = Olimpiada::where('gestion', date('Y'))->first()
                     ?? Olimpiada::latest('id_olimpiada')->first();

        if (!$olimpiada) {
            $this->command->error('❌ No se encontró olimpiada activa.');
            return;
        }

        $this->command->info("Configurando acciones para: {$olimpiada->nombre}");

        $faseConfig = FaseGlobal::where('codigo', 'CONFIG')->where('id_olimpiada', $olimpiada->id_olimpiada)->first();
        $faseEval   = FaseGlobal::where('codigo', 'EVAL')->where('id_olimpiada', $olimpiada->id_olimpiada)->first();
        $faseFinal  = FaseGlobal::where('codigo', 'FINAL')->where('id_olimpiada', $olimpiada->id_olimpiada)->first();

        $accionRegEstud   = AccionSistema::where('codigo', 'REG_ESTUD')->first();
        $accionCargarNotas= AccionSistema::where('codigo', 'CARGAR_NOTAS')->first();
        $accionPubClasif  = AccionSistema::where('codigo', 'PUB_CLASIF')->first();

        if (!$faseConfig || !$accionRegEstud) {
            $this->command->warn('⚠️ Faltan datos base (Fases o Acciones). Ejecuta FaseGlobalSeeder y AccionSistemaSeeder primero.');
            return;
        }

        $matrizConfiguracion = [

            ['fase' => $faseConfig, 'accion' => $accionRegEstud, 'habilitada' => true],
            ['fase' => $faseConfig, 'accion' => $accionCargarNotas, 'habilitada' => false],
            ['fase' => $faseConfig, 'accion' => $accionPubClasif, 'habilitada' => false],
            ['fase' => $faseEval, 'accion' => $accionRegEstud, 'habilitada' => false],
            ['fase' => $faseEval, 'accion' => $accionCargarNotas, 'habilitada' => true],
            ['fase' => $faseEval, 'accion' => $accionPubClasif, 'habilitada' => false],
            ['fase' => $faseFinal, 'accion' => $accionRegEstud, 'habilitada' => false],
            ['fase' => $faseFinal, 'accion' => $accionCargarNotas, 'habilitada' => true],
            ['fase' => $faseFinal, 'accion' => $accionPubClasif, 'habilitada' => true],
        ];

        foreach ($matrizConfiguracion as $item) {
            $fase = $item['fase'];
            $accion = $item['accion'];
            $habilitada = $item['habilitada'];

            if ($fase && $accion) {
                ConfiguracionAccion::firstOrCreate(
                    [
                        'id_fase_global'    => $fase->id_fase_global,
                        'id_accion_sistema' => $accion->id_accion_sistema,
                    ],
                    [
                        'habilitada' => $habilitada
                    ]
                );
            }
        }

        $this->command->info('✅ Configuración de acciones inicializada correctamente.');
    }
}
