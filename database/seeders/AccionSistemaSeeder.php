<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\AccionSistema;

class AccionSistemaSeeder extends Seeder
{

    public function run(): void
    {
        $acciones = [
            [
                'codigo'      => 'REG_ESTUD',
                'nombre'      => 'Registrar estudiantes',
                'descripcion' => 'Permite a los responsables registrar a sus estudiantes en la olimpiada.',
            ],
            [
                'codigo'      => 'CARGAR_NOTAS',
                'nombre'      => 'Cargar notas',
                'descripcion' => 'Permite a los evaluadores cargar las notas de las evaluaciones de los estudiantes.',
            ],
            [
                'codigo'      => 'PUB_CLASIF',
                'nombre'      => 'Publicar clasificados',
                'descripcion' => 'Permite publicar la lista de estudiantes que clasificaron a la siguiente fase.',
            ],
        ];

        $this->command->info('⚙️ Configurando acciones del sistema...');

        foreach ($acciones as $data) {

            AccionSistema::firstOrCreate(
                ['codigo' => $data['codigo']],
                [
                    'nombre'      => $data['nombre'],
                    'descripcion' => $data['descripcion']
                ]
            );
        }

        $this->command->info('✅ Acciones del sistema listas.');
    }
}
