<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Model\Olimpiada;
use App\Model\Area;
use App\Model\Nivel;
use App\Model\GradoEscolaridad;
use App\Model\Persona;
use App\Model\Usuario;
use App\Model\Rol;
use App\Model\Institucion;
use App\Model\AreaOlimpiada;
use App\Model\AreaNivel;
use App\Model\EvaluadorAn;
use App\Model\Competidor;
use App\Model\Departamento;

class Olimpiadas2024Seeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('🚀 Iniciando seeder para la Olimpiada 2024...');

            $olimpiada = Olimpiada::firstOrCreate(['gestion' => '2024'], ['nombre' => 'Olimpiada 2024', 'estado' => false]);
            $area = Area::firstOrCreate(['nombre' => 'Química']);
            $nivel = Nivel::firstOrCreate(['nombre' => 'Nivel 1']);
            $grado = GradoEscolaridad::firstOrCreate(['nombre' => '1ro de Secundaria']);

            $ao = AreaOlimpiada::firstOrCreate(['id_area' => $area->id_area, 'id_olimpiada' => $olimpiada->id_olimpiada]);
            $an = AreaNivel::firstOrCreate(['id_area_olimpiada' => $ao->id_area_olimpiada, 'id_nivel' => $nivel->id_nivel], ['es_activo' => true]);
            $an->gradosEscolaridad()->syncWithoutDetaching([$grado->id_grado_escolaridad]);

            $pResp = Persona::firstOrCreate(['ci' => '9988777'], ['nombre' => 'Roberto', 'apellido' => 'Gomez', 'email' => 'rob@test.com', 'telefono' => '777']);
            $uResp = Usuario::firstOrCreate(['email' => 'rob@test.com'], ['id_persona' => $pResp->id_persona, 'password' => '123']);

            $inst = Institucion::firstOrCreate(['nombre' => 'San Agustin']);
            $depto = Departamento::firstOrCreate(['nombre' => 'La Paz']);

            $p = Persona::firstOrCreate(['ci' => '5678901'], [
                'nombre' => 'Pedro', 'apellido' => 'Lopez', 'email' => 'pedro@test.com', 'telefono' => '000'
            ]);

            Competidor::firstOrCreate(['id_persona' => $p->id_persona, 'id_area_nivel' => $an->id_area_nivel], [
                'id_institucion' => $inst->id_institucion,
                'id_departamento' => $depto->id_departamento,
                'id_grado_escolaridad' => $grado->id_grado_escolaridad,
                'contacto_tutor' => 'Tutor',
                'genero' => 'M',
                'estado_evaluacion' => 'disponible'
            ]);

            $this->command->info('✅ Seeder Olimpiada 2024 completado.');
        });
    }
}
