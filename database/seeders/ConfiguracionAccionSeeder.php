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
        // 1. Obtener la Olimpiada objetivo (Gestión actual o última creada)
        $olimpiada = Olimpiada::where('gestion', date('Y'))->first()
                     ?? Olimpiada::latest('id_olimpiada')->first();

        if (!$olimpiada) {
            $this->command->error('❌ No se encontró olimpiada activa.');
            return;
        }

        $this->command->info("Configurando acciones para: {$olimpiada->nombre}");

        // 2. Obtener Fases filtradas por la olimpiada correcta
        // Usamos first() porque asumimos que solo hay una fase de cada tipo por olimpiada
        $faseConfig = FaseGlobal::where('codigo', 'CONFIG')->where('id_olimpiada', $olimpiada->id_olimpiada)->first();
        $faseEval   = FaseGlobal::where('codigo', 'EVAL')->where('id_olimpiada', $olimpiada->id_olimpiada)->first();
        $faseFinal  = FaseGlobal::where('codigo', 'FINAL')->where('id_olimpiada', $olimpiada->id_olimpiada)->first();

        // 3. Obtener Acciones del Sistema (Catálogo estático)
        $accionRegEstud   = AccionSistema::where('codigo', 'REG_ESTUD')->first();
        $accionCargarNotas= AccionSistema::where('codigo', 'CARGAR_NOTAS')->first();
        $accionPubClasif  = AccionSistema::where('codigo', 'PUB_CLASIF')->first();

        // Validamos que existan datos mínimos antes de continuar
        if (!$faseConfig || !$accionRegEstud) {
            $this->command->warn('⚠️ Faltan datos base (Fases o Acciones). Ejecuta FaseGlobalSeeder y AccionSistemaSeeder primero.');
            return;
        }

        // 4. Matriz de Configuración
        // Definimos qué acciones están habilitadas en cada fase
        $matrizConfiguracion = [
            // --- Fase de Configuración ---
            // Solo se permite registrar estudiantes
            ['fase' => $faseConfig, 'accion' => $accionRegEstud, 'habilitada' => true],
            ['fase' => $faseConfig, 'accion' => $accionCargarNotas, 'habilitada' => false],
            ['fase' => $faseConfig, 'accion' => $accionPubClasif, 'habilitada' => false],

            // --- Fase de Evaluación ---
            // Se cierran registros, se abren notas
            ['fase' => $faseEval, 'accion' => $accionRegEstud, 'habilitada' => false],
            ['fase' => $faseEval, 'accion' => $accionCargarNotas, 'habilitada' => true],
            ['fase' => $faseEval, 'accion' => $accionPubClasif, 'habilitada' => false],

            // --- Fase Final ---
            // Se permiten reportes finales y ajustes de notas si es necesario
            ['fase' => $faseFinal, 'accion' => $accionRegEstud, 'habilitada' => false],
            ['fase' => $faseFinal, 'accion' => $accionCargarNotas, 'habilitada' => true], // A veces se cargan notas de la final
            ['fase' => $faseFinal, 'accion' => $accionPubClasif, 'habilitada' => true],   // Se publican ganadores
        ];

        // 5. Insertar Configuración
        foreach ($matrizConfiguracion as $item) {
            $fase = $item['fase'];
            $accion = $item['accion'];
            $habilitada = $item['habilitada'];

            if ($fase && $accion) {
                // firstOrCreate verifica si ya existe la combinación fase-acción para no duplicar
                ConfiguracionAccion::firstOrCreate(
                    [
                        // Claves de búsqueda (Unique Constraint implícito)
                        'id_fase_global'    => $fase->id_fase_global,
                        'id_accion_sistema' => $accion->id_accion_sistema,
                    ],
                    [
                        // Valores a insertar si no existe
                        'habilitada' => $habilitada
                    ]
                );
            }
        }

        $this->command->info('✅ Configuración de acciones inicializada correctamente.');
    }
}
