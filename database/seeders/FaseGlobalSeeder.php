<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\FaseGlobal;
use App\Model\Olimpiada;

class FaseGlobalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $olimpiada = Olimpiada::where('gestion', date('Y'))->first()
                     ?? Olimpiada::latest('id_olimpiada')->first();

        $idOlimpiada = $olimpiada ? $olimpiada->id_olimpiada : null;

        $fases = [
            [
                'codigo' => 'CONFIG',
                'nombre' => 'Fase de Configuración',
                'orden'  => 1,
            ],
            [
                'codigo' => 'EVAL',
                'nombre' => 'Fase de Calificación',
                'orden'  => 2,
            ],
            [
                'codigo' => 'FINAL',
                'nombre' => 'Fase Final',
                'orden'  => 3,
            ],
        ];

        $this->command->info('Generando fases globales...');

        foreach ($fases as $fase) {
            FaseGlobal::firstOrCreate(
                [
                    'codigo'       => $fase['codigo'],
                    'id_olimpiada' => $idOlimpiada
                ],
                [
                    'nombre' => $fase['nombre'],
                    'orden'  => $fase['orden']
                ]
            );
        }

        $this->command->info('✅ Fases globales creadas correctamente.');
    }
}
