<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccionSistemaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('accion_sistema')->insert([
            [
                'id_accion' => 10,
                'codigo' => 'REG_ESTUD',
                'nombre' => 'Registrar estudiantes',
                'descripcion' => 'Permite a los responsables registrar a sus estudiantes en la olimpiada.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id_accion' => 20,
                'codigo' => 'CARGAR_NOTAS',
                'nombre' => 'Cargar notas',
                'descripcion' => 'Permite a los evaluadores cargar las notas de las evaluaciones de los estudiantes.',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id_accion' => 30,
                'codigo' => 'PUB_CLASIF',
                'nombre' => 'Publicar clasificados',
                'descripcion' => 'Permite publicar la lista de estudiantes que clasificaron a la siguiente fase.',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
