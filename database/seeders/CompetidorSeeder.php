<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Exception;
use Faker\Factory as Faker;

use App\Model\Competidor;
use App\Model\Persona;
use App\Model\Institucion;
use App\Model\AreaNivel;
use App\Model\GradoEscolaridad;
use App\Model\ArchivoCsv;
use App\Model\Olimpiada;
use App\Model\Departamento;

class CompetidorSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');

        DB::beginTransaction();
        try {
            $olimpiada = Olimpiada::where('gestion', date('Y'))->first();
            if (!$olimpiada) {
                $olimpiada = Olimpiada::latest('id_olimpiada')->first();
            }
            if (!$olimpiada) {
                $this->command->warn("❗ No existe ninguna olimpiada. Se crea una de prueba para " . date('Y'));
                $olimpiada = Olimpiada::create([
                    'nombre' => 'Olimpiada Prueba',
                    'gestion' => date('Y'),
                    'estado' => true,
                ]);
            }

            $idsDepartamentos = Departamento::pluck('id_departamento')->toArray();
            if (empty($idsDepartamentos)) {
                $this->command->warn('⚠️ No hay departamentos. Se crean departamentos de ejemplo.');
                $deptos = ['La Paz','Cochabamba','Santa Cruz','Oruro','Potosí','Chuquisaca','Pando','Tarija','Beni'];
                foreach ($deptos as $nombre) {
                    Departamento::firstOrCreate(['nombre' => $nombre]);
                }
                $idsDepartamentos = Departamento::pluck('id_departamento')->toArray();
            }

            if (Institucion::count() == 0) {
                $this->command->warn('⚠️ No hay instituciones. Creando instituciones dummy.');
                $institucionesDummy = ['Colegio A', 'Colegio B', 'Colegio C'];
                foreach ($institucionesDummy as $nombre) {
                    Institucion::firstOrCreate(['nombre' => $nombre]);
                }
            }
            $idsInstituciones = Institucion::pluck('id_institucion')->toArray();

            if (ArchivoCsv::count() == 0) {
                ArchivoCsv::create(['nombre' => "import_test.csv", 'fecha' => now()]);
            }
            $idsArchivos = ArchivoCsv::pluck('id_archivo_csv')->toArray();

            if (DB::table('area')->count() == 0) {
                DB::table('area')->insert(['nombre' => 'Matemáticas', 'created_at' => now(), 'updated_at' => now()]);
            }
            $idAreaExistente = DB::table('area')->orderBy('id_area','asc')->value('id_area');

            if (DB::table('area_olimpiada')->count() == 0) {
                DB::table('area_olimpiada')->insert([
                    'id_area' => $idAreaExistente,
                    'id_olimpiada' => $olimpiada->id_olimpiada,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            $idAreaOlimpiadaExistente = DB::table('area_olimpiada')->orderBy('id_area_olimpiada','asc')->value('id_area_olimpiada');

            if (DB::table('nivel')->count() == 0) {
                DB::table('nivel')->insert(['nombre' => 'Nivel 1', 'created_at' => now(), 'updated_at' => now()]);
            }
            $idNivelExistente = DB::table('nivel')->orderBy('id_nivel','asc')->value('id_nivel');

            if (DB::table('area_nivel')->count() == 0) {
                DB::table('area_nivel')->insert([
                    [
                        'id_area_olimpiada' => $idAreaOlimpiadaExistente,
                        'id_nivel' => $idNivelExistente,
                        'es_activo' => true,
                        'created_at' => now(),
                        'updated_at' => now()
                    ],
                ]);
            }

            if (GradoEscolaridad::count() == 0) {
                $this->command->warn('⚠️ No hay grados. Creando algunos grados de ejemplo.');
                $grados = ['1ro', '2do', '3ro', '4to', '5to'];
                foreach ($grados as $g) {
                    GradoEscolaridad::firstOrCreate(['nombre' => $g]);
                }
            }
            $idsGrados = GradoEscolaridad::pluck('id_grado_escolaridad')->toArray();

            if (empty($idsGrados)) {
                $this->command->error('❌ No hay grados disponibles.');
                DB::rollBack();
                return;
            }

            $areasNivelesValidos = DB::table('area_nivel as an')
                ->join('area_olimpiada as ao', 'an.id_area_olimpiada', '=', 'ao.id_area_olimpiada')
                ->join('area as a', 'ao.id_area', '=', 'a.id_area')
                ->join('nivel as n', 'an.id_nivel', '=', 'n.id_nivel')
                ->select(
                    'an.id_area_nivel',
                    'an.id_area_olimpiada',
                    'an.id_nivel',
                    'ao.id_area',
                    'a.nombre as area_nombre',
                    'n.nombre as nivel_nombre'
                )
                ->get()
                ->toArray();

            if (empty($areasNivelesValidos)) {
                $this->command->error('❌ No existen registros válidos en area_nivel.');
                DB::rollBack();
                return;
            }

            $idsAreaNiveles = array_map(fn($x) => $x->id_area_nivel, $areasNivelesValidos);
            $areaNivelGradosRaw = DB::table('area_nivel_grado')
                ->whereIn('id_area_nivel', $idsAreaNiveles)
                ->get();

            $mapAreaNivelToGrados = [];
            foreach ($areaNivelGradosRaw as $row) {
                $mapAreaNivelToGrados[$row->id_area_nivel][] = $row->id_grado_escolaridad;
            }

            foreach ($idsAreaNiveles as $idAn) {
                if (empty($mapAreaNivelToGrados[$idAn])) {
                    $numeroAsociaciones = 1;
                    $gradosParaAsociar = (array) array_slice($idsGrados, 0, $numeroAsociaciones);
                    foreach ($gradosParaAsociar as $idGr) {
                        DB::table('area_nivel_grado')->insert([
                            'id_area_nivel' => $idAn,
                            'id_grado_escolaridad' => $idGr,
                        ]);
                        $mapAreaNivelToGrados[$idAn][] = $idGr;
                    }
                }
            }

            $cantidad = 50;
            $this->command->info("Generando $cantidad competidores (áreas+grados coherentes)...");
            $this->command->getOutput()->progressStart($cantidad);

            for ($i = 0; $i < $cantidad; $i++) {
                $generoReal = $faker->randomElement(['M', 'F']);

                $ci = substr($faker->unique()->numerify('###########'), 0, 15);

                $persona = Persona::create([
                    'nombre' => $faker->firstName($generoReal == 'M' ? 'male' : 'female'),
                    'apellido' => $faker->lastName,
                    'ci' => $ci,
                    'telefono' => $faker->numerify('7##########'),
                    'email' => $faker->unique()->safeEmail,
                ]);

                $eleccion = $areasNivelesValidos[array_rand($areasNivelesValidos)];
                $idAreaNivelSeleccionado = $eleccion->id_area_nivel;

                $gradosValidos = $mapAreaNivelToGrados[$idAreaNivelSeleccionado] ?? $idsGrados;

                $idGradoSeleccionado = $gradosValidos[array_rand($gradosValidos)];

                $nombreTutor = mb_substr($faker->name, 0, 15);

                Competidor::create([
                    'id_persona'           => $persona->id_persona,
                    'id_institucion'       => $faker->randomElement($idsInstituciones),
                    'id_departamento'      => $faker->randomElement($idsDepartamentos),
                    'id_area_nivel'        => $idAreaNivelSeleccionado,
                    'id_grado_escolaridad' => $idGradoSeleccionado,
                    'id_archivo_csv'       => !empty($idsArchivos) ? $faker->randomElement($idsArchivos) : null,
                    'contacto_tutor'       => $nombreTutor,
                    'genero'               => $generoReal,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);

                $this->command->getOutput()->progressAdvance();
            }

            $this->command->getOutput()->progressFinish();
            $this->command->info('✅ Competidores creados exitosamente (con áreas y grados coherentes).');

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error al crear competidores: ' . $e->getMessage());
            throw $e;
        }
    }
}
