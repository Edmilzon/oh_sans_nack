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
use App\Model\Fase;
use App\Model\Parametro;
use App\Model\ResponsableArea;
use App\Model\EvaluadorAn;
use App\Model\Competidor;
use App\Model\Evaluacion;
use App\Model\Grupo;
use App\Model\Competencia;
use App\Model\Medallero;
use App\Model\Desclasificacion;
use App\Model\Aval;

class Olimpiada2021Seeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('Iniciando seeder para la Olimpiada 2021...');

            // 1. Crear Grados de Escolaridad
            $gradosEscolaridad = [
                ['nombre' => '1ro Básico'],
                ['nombre' => '2do Básico'],
                ['nombre' => '3ro Básico'],
                ['nombre' => '4to Básico'],
                ['nombre' => '5to Básico'],
                ['nombre' => '6to Básico'],
                ['nombre' => '1ro de Secundaria'],
                ['nombre' => '2do de Secundaria'],
                ['nombre' => '3ro de Secundaria'],
                ['nombre' => '4to de Secundaria'],
                ['nombre' => '5to de Secundaria'],
                ['nombre' => '6to de Secundaria'],
            ];

            foreach ($gradosEscolaridad as $grado) {
                GradoEscolaridad::firstOrCreate(['nombre' => $grado['nombre']]);
            }

            $grado1ro = GradoEscolaridad::where('nombre', '1ro Básico')->first();
            $grado2do = GradoEscolaridad::where('nombre', '2do Básico')->first();
            $grado3ro = GradoEscolaridad::where('nombre', '3ro Básico')->first();
            $grado4to = GradoEscolaridad::where('nombre', '4to Básico')->first();
            $grado5to = GradoEscolaridad::where('nombre', '5to Básico')->first();
            $grado6to = GradoEscolaridad::where('nombre', '6to Básico')->first();
            $grado7mo = GradoEscolaridad::where('nombre', '1ro de Secundaria')->first();
            $grado8vo = GradoEscolaridad::where('nombre', '2do de Secundaria')->first();
            $grado9no = GradoEscolaridad::where('nombre', '3ro de Secundaria')->first();
            $grado10mo = GradoEscolaridad::where('nombre', '4to de Secundaria')->first();
            $grado11vo = GradoEscolaridad::where('nombre', '5to de Secundaria')->first();
            $grado12vo = GradoEscolaridad::where('nombre', '6to de Secundaria')->first();

            // 2. Crear la Olimpiada
            $olimpiada = Olimpiada::create([
                'nombre' => 'Olimpiada Científica Estudiantil 2021',
                'gestion' => '2021',
            ]);
            $this->command->info("Olimpiada '{$olimpiada->nombre}' creada.");

            // 3. Obtener Areas y Niveles
            $areas = Area::whereIn('nombre', ['Matemáticas', 'Física', 'Informática', 'Química', 'Biología', 'Arte'])->get();
            if ($areas->isEmpty()) {
                $this->command->error('No se encontraron áreas. Crea algunas áreas primero.');
                return;
            }
            $niveles = Nivel::all();
            if ($niveles->isEmpty()) {
                $this->command->error('No se encontraron niveles. Crea algunos niveles primero.');
                return;
            }

            // 4. Vincular Áreas con la Olimpiada 2021
            $areaOlimpiadaIds = [];
            foreach ($areas as $area) {
                $areaOlimpiada = AreaOlimpiada::create([
                    'id_area' => $area->id_area,
                    'id_olimpiada' => $olimpiada->id_olimpiada,
                ]);
                $areaOlimpiadaIds[$area->nombre] = $areaOlimpiada->id_area_olimpiada;
            }
            $this->command->info('Áreas vinculadas a la olimpiada.');

            // 5. Crear Personas primero
            $personasData = [
                ['nombre' => 'Zimme', 'apellido' => 'Castro', 'ci' => '6778891', 'email' => 'zimme.castro@test.com', 'genero' => 'M', 'telefono' => '78657123'],
                ['nombre' => 'Sandra', 'apellido' => 'Bullock', 'ci' => '6546673', 'email' => 'sandra.bullock@test.com', 'genero' => 'F', 'telefono' => '78800727'],
                
            ];

            $personas = [];
            foreach ($personasData as $data) {
                $personas[] = Persona::create($data);
            }

            // 6. Crear Usuarios
            $responsableUser = Usuario::create([
                'nombre' => 'Zimme',
                'apellido' => 'Castro', 
                'ci' => '6778891', 
                'email' => 'zimme.castro@test.com', 
                'password' => bcrypt('mundolibre'),
                'telefono' => '78657123'
            ]);

            $evaluadorUser = Usuario::create([
                'nombre' => 'Sandra',
                'apellido' => 'Bullock', 
                'ci' => '6546673', 
                'email' => 'sandra.bullock@test.com', 
                'password' => bcrypt('password12'),
                'telefono' => '7800727'
            ]);

            // Asignar roles
            $rolResponsable = Rol::where('nombre', 'Responsable Area')->first();
            $rolEvaluador = Rol::where('nombre', 'Evaluador')->first();

            if ($rolResponsable && $rolEvaluador) {
                DB::table('usuario_rol')->insert([
                    ['id_usuario' => $responsableUser->id_usuario, 'id_rol' => $rolResponsable->id_rol, 'id_olimpiada' => $olimpiada->id_olimpiada],
                    ['id_usuario' => $evaluadorUser->id_usuario, 'id_rol' => $rolEvaluador->id_rol, 'id_olimpiada' => $olimpiada->id_olimpiada],
                ]);
            }
            $this->command->info('Usuarios creados.');

            // 7. Vincular usuarios a sus áreas
            $responsableArea = ResponsableArea::create([
                'id_usuario' => $responsableUser->id_usuario,
                'id_area_olimpiada' => $areaOlimpiadaIds['Matemáticas'],
            ]);
            
            $responsableAreaFis = ResponsableArea::create([
                'id_usuario' => $responsableUser->id_usuario,
                'id_area_olimpiada' => $areaOlimpiadaIds['Física'],
            ]);
            
            // 8. Crear Personas para competidores
            $personasCompetidoresData = [
                ['nombre' => 'Ana', 'apellido' => 'Vacadiaz', 'ci' => '86985436', 'email' => 'ana.vacadiaz@test.com', 'genero' => 'F', 'telefono' => '77799009'],
                ['nombre' => 'Juan', 'apellido' => 'Chaverrancho', 'ci' => '64567890', 'email' => 'juan.chaverrancho', 'genero' => 'M', 'telefono' => '77791990'],
                ['nombre' => 'Sofia', 'apellido' => 'Villalobos', 'ci' => '15678901', 'email' => 'sofia.villalobos@test.com', 'genero' => 'F', 'telefono' => '77792901'],
                ['nombre' => 'Mateo', 'apellido' => 'Quispe', 'ci' => '56989012', 'email' => 'mateo.quispe@test.com', 'genero' => 'M', 'telefono' => '77793909'],
                ['nombre' => 'Pepe', 'apellido' => 'Castañeda', 'ci' => '5690901', 'email' => 'pepe.castañeda@test.com', 'genero' => 'M', 'telefono' => '77700017'],
                ['nombre' => 'Pedro', 'apellido' => 'Costas', 'ci' => '8901111', 'email' => 'pedro.costas@test.com', 'genero' => 'M', 'telefono' => '77681924'],
                ['nombre' => 'Fabiola', 'apellido' => 'Luna', 'ci' => '3456711', 'email' => 'fabila.luna@test.com', 'genero' => 'F', 'telefono' => '77919100'],
                ['nombre' => 'Raiza ', 'apellido' => 'Rodriguez', 'ci' => '4786823', 'email' => 'raiza.rodriguez@test.com', 'genero' => 'F', 'telefono' => '71818123'],

                ['nombre' => 'Elena', 'apellido' => 'Paredes', 'ci' => '1000010', 'email' => 'elena.paredes@test.com', 'genero' => 'F', 'telefono' => '77711110'],
                ['nombre' => 'Diego', 'apellido' => 'Castro', 'ci' => '1000013', 'email' => 'diego.castro@test.com', 'genero' => 'M', 'telefono' => '77711323'],
                ['nombre' => 'Gabriela', 'apellido' => 'Rojas', 'ci' => '1000014', 'email' => 'gabriela.rojas@test.com', 'genero' => 'F', 'telefono' => '77711324'],
                ['nombre' => 'Fernando', 'apellido' => 'Vargas', 'ci' => '1000015', 'email' => 'fernando.vargas@test.com', 'genero' => 'M', 'telefono' => '77711325'],
                ['nombre' => 'Isabel', 'apellido' => 'Morales', 'ci' => '1000016', 'email' => 'isabel.morales@test.com', 'genero' => 'F', 'telefono' => '77711326'],
                ['nombre' => 'Ricardo', 'apellido' => 'Suarez', 'ci' => '1000017', 'email' => 'ricardo.suarez@test.com', 'genero' => 'M', 'telefono' => '77711327'],
                ['nombre' => 'Patricia', 'apellido' => 'Cruz', 'ci' => '1000018', 'email' => 'patricia.cruz@test.com', 'genero' => 'F', 'telefono' => '77711328'],
                ['nombre' => 'Javier', 'apellido' => 'Ortega', 'ci' => '1000019', 'email' => 'javier.ortega@test.com', 'genero' => 'M', 'telefono' => '77711329'],
                ['nombre' => 'Carmen', 'apellido' => 'Navarro', 'ci' => '1000020', 'email' => 'carmen.navarro@test.com', 'genero' => 'F', 'telefono' => '77713220'],
                ['nombre' => 'Alejandro', 'apellido' => 'Romero', 'ci' => '1000021', 'email' => 'alejandro.romero@test.com', 'genero' => 'M', 'telefono' => '77711321'],
                ['nombre' => 'Raquel', 'apellido' => 'Aguilar', 'ci' => '1000022', 'email' => 'raquel.aguilar@test.com', 'genero' => 'F', 'telefono' => '77711322'],
                ['nombre' => 'Mauricio', 'apellido' => 'Santos', 'ci' => '1000023', 'email' => 'mauricio.santos@test.com', 'genero' => 'M', 'telefono' => '7771133'],
                ['nombre' => 'Daniela', 'apellido' => 'Cordova', 'ci' => '1000024', 'email' => 'daniela.cordova@test.com', 'genero' => 'F', 'telefono' => '7771134'],
                ['nombre' => 'Oscar', 'apellido' => 'Ponce', 'ci' => '1000025', 'email' => 'oscar.ponce@test.com', 'genero' => 'M', 'telefono' => '7771135'],
                ['nombre' => 'Veronica', 'apellido' => 'Velasco', 'ci' => '1000026', 'email' => 'veronica.velasco@test.com', 'genero' => 'F', 'telefono' => '7771136'],
                ['nombre' => 'Hector', 'apellido' => 'Zambrana', 'ci' => '1000027', 'email' => 'hector.zambrana@test.com', 'genero' => 'M', 'telefono' => '7771137'],
                ['nombre' => 'Natalia', 'apellido' => 'Quiroga', 'ci' => '1000028', 'email' => 'natalia.quiroga@test.com', 'genero' => 'F', 'telefono' => '7771138'],
                ['nombre' => 'Pablo', 'apellido' => 'Salinas', 'ci' => '1000029', 'email' => 'pablo.salinas@test.com', 'genero' => 'M', 'telefono' => '7771139'],
                ['nombre' => 'Adriana', 'apellido' => 'Perez', 'ci' => '1000030', 'email' => 'adriana.perez@test.com', 'genero' => 'F', 'telefono' => '7771130'],

    // Segundo bloque - Física (25 estudiantes)
    ['nombre' => 'Luis', 'apellido' => 'Fernandez', 'ci' => '1000031', 'email' => 'luis.fernandez@test.com', 'genero' => 'M', 'telefono' => '77711131'],
    ['nombre' => 'Monica', 'apellido' => 'Garcia', 'ci' => '1000032', 'email' => 'monica.garcia@test.com', 'genero' => 'F', 'telefono' => '77711132'],
    ['nombre' => 'Raul', 'apellido' => 'Diaz', 'ci' => '1000033', 'email' => 'raul.diaz@test.com', 'genero' => 'M', 'telefono' => '77711133'],
    ['nombre' => 'Silvia', 'apellido' => 'Martinez', 'ci' => '1000034', 'email' => 'silvia.martinez@test.com', 'genero' => 'F', 'telefono' => '77711134'],
    ['nombre' => 'Alberto', 'apellido' => 'Gomez', 'ci' => '1000035', 'email' => 'alberto.gomez@test.com', 'genero' => 'M', 'telefono' => '77711135'],
    ['nombre' => 'Claudia', 'apellido' => 'Herrera', 'ci' => '1000036', 'email' => 'claudia.herrera@test.com', 'genero' => 'F', 'telefono' => '77711136'],
    ['nombre' => 'Jorge', 'apellido' => 'Reyes', 'ci' => '1000037', 'email' => 'jorge.reyes@test.com', 'genero' => 'M', 'telefono' => '77711137'],
    ['nombre' => 'Teresa', 'apellido' => 'Castro', 'ci' => '1000038', 'email' => 'teresa.castro@test.com', 'genero' => 'F', 'telefono' => '77711138'],
    ['nombre' => 'Miguel', 'apellido' => 'Villarroel', 'ci' => '1000039', 'email' => 'miguel.villarroel@test.com', 'genero' => 'M', 'telefono' => '77711139'],
    ['nombre' => 'Eva', 'apellido' => 'Lara', 'ci' => '1000040', 'email' => 'eva.lara@test.com', 'genero' => 'F', 'telefono' => '77711140'],
    ['nombre' => 'Samuel', 'apellido' => 'Camacho', 'ci' => '1000041', 'email' => 'samuel.camacho@test.com', 'genero' => 'M', 'telefono' => '77711141'],
    ['nombre' => 'Rosa', 'apellido' => 'Miranda', 'ci' => '1000042', 'email' => 'rosa.miranda@test.com', 'genero' => 'F', 'telefono' => '77711142'],
    ['nombre' => 'Victor', 'apellido' => 'Arancibia', 'ci' => '1000043', 'email' => 'victor.arancibia@test.com', 'genero' => 'M', 'telefono' => '77711143'],
    ['nombre' => 'Julia', 'apellido' => 'Escobar', 'ci' => '1000044', 'email' => 'julia.escobar@test.com', 'genero' => 'F', 'telefono' => '77711144'],
    ['nombre' => 'Francisco', 'apellido' => 'Pinto', 'ci' => '1000045', 'email' => 'francisco.pinto@test.com', 'genero' => 'M', 'telefono' => '77711145'],
    ['nombre' => 'Sara', 'apellido' => 'Mendez', 'ci' => '1000046', 'email' => 'sara.mendez@test.com', 'genero' => 'F', 'telefono' => '77711146'],
    ['nombre' => 'Hugo', 'apellido' => 'Orellana', 'ci' => '1000047', 'email' => 'hugo.orellana@test.com', 'genero' => 'M', 'telefono' => '77711147'],
    ['nombre' => 'Beatriz', 'apellido' => 'Valdez', 'ci' => '1000048', 'email' => 'beatriz.valdez@test.com', 'genero' => 'F', 'telefono' => '77711148'],
    ['nombre' => 'Rodrigo', 'apellido' => 'Cabrera', 'ci' => '1000049', 'email' => 'rodrigo.cabrera@test.com', 'genero' => 'M', 'telefono' => '77711149'],
    ['nombre' => 'Olga', 'apellido' => 'Fuentes', 'ci' => '1000050', 'email' => 'olga.fuentes@test.com', 'genero' => 'F', 'telefono' => '77711150'],
    ['nombre' => 'Esteban', 'apellido' => 'Ramos', 'ci' => '1000051', 'email' => 'esteban.ramos@test.com', 'genero' => 'M', 'telefono' => '77711151'],
    ['nombre' => 'Lorena', 'apellido' => 'Molina', 'ci' => '1000052', 'email' => 'lorena.molina@test.com', 'genero' => 'F', 'telefono' => '77711152'],
    ['nombre' => 'Felipe', 'apellido' => 'Caceres', 'ci' => '1000053', 'email' => 'felipe.caceres@test.com', 'genero' => 'M', 'telefono' => '77711153'],
    ['nombre' => 'Ruth', 'apellido' => 'Pacheco', 'ci' => '1000054', 'email' => 'ruth.pacheco@test.com', 'genero' => 'F', 'telefono' => '77711154'],
    ['nombre' => 'Mario', 'apellido' => 'Tapia', 'ci' => '1000055', 'email' => 'mario.tapia@test.com', 'genero' => 'M', 'telefono' => '77711155'],

    // Tercer bloque - Química (20 estudiantes)
    ['nombre' => 'Andrea', 'apellido' => 'Rivera', 'ci' => '1000056', 'email' => 'andrea.rivera@test.com', 'genero' => 'F', 'telefono' => '77711156'],
    ['nombre' => 'Gustavo', 'apellido' => 'Medina', 'ci' => '1000057', 'email' => 'gustavo.medina@test.com', 'genero' => 'M', 'telefono' => '77711157'],
    ['nombre' => 'Carolina', 'apellido' => 'Vega', 'ci' => '1000058', 'email' => 'carolina.vega@test.com', 'genero' => 'F', 'telefono' => '77711158'],
    ['nombre' => 'Leonardo', 'apellido' => 'Espinoza', 'ci' => '1000059', 'email' => 'leonardo.espinoza@test.com', 'genero' => 'M', 'telefono' => '77711159'],
    ['nombre' => 'Diana', 'apellido' => 'Castillo', 'ci' => '1000060', 'email' => 'diana.castillo@test.com', 'genero' => 'F', 'telefono' => '77711160'],
    ['nombre' => 'Marcelo', 'apellido' => 'Guerrero', 'ci' => '1000061', 'email' => 'marcelo.guerrero@test.com', 'genero' => 'M', 'telefono' => '77711161'],
    ['nombre' => 'Valeria', 'apellido' => 'Ortiz', 'ci' => '1000062', 'email' => 'valeria.ortiz@test.com', 'genero' => 'F', 'telefono' => '77711162'],
    ['nombre' => 'Rafael', 'apellido' => 'Silva', 'ci' => '1000063', 'email' => 'rafael.silva@test.com', 'genero' => 'M', 'telefono' => '77711163'],
    ['nombre' => 'Paola', 'apellido' => 'Carrasco', 'ci' => '1000064', 'email' => 'paola.carrasco@test.com', 'genero' => 'F', 'telefono' => '77711164'],
    ['nombre' => 'Santiago', 'apellido' => 'Parada', 'ci' => '1000065', 'email' => 'santiago.parada@test.com', 'genero' => 'M', 'telefono' => '77711165'],
    ['nombre' => 'Nadia', 'apellido' => 'Rocha', 'ci' => '1000066', 'email' => 'nadia.rocha@test.com', 'genero' => 'F', 'telefono' => '77711166'],
    ['nombre' => 'Cesar', 'apellido' => 'Maldonado', 'ci' => '1000067', 'email' => 'cesar.maldonado@test.com', 'genero' => 'M', 'telefono' => '77711167'],
    ['nombre' => 'Jimena', 'apellido' => 'Aguirre', 'ci' => '1000068', 'email' => 'jimena.aguirre@test.com', 'genero' => 'F', 'telefono' => '77711168'],
    ['nombre' => 'Andres', 'apellido' => 'Villanueva', 'ci' => '1000069', 'email' => 'andres.villanueva@test.com', 'genero' => 'M', 'telefono' => '77711169'],
    ['nombre' => 'Lucia', 'apellido' => 'Peralta', 'ci' => '1000070', 'email' => 'lucia.peralta@test.com', 'genero' => 'F', 'telefono' => '77711170'],
    ['nombre' => 'Emilio', 'apellido' => 'Zapata', 'ci' => '1000071', 'email' => 'emilio.zapata@test.com', 'genero' => 'M', 'telefono' => '77711171'],
    ['nombre' => 'Marcela', 'apellido' => 'Rivas', 'ci' => '1000072', 'email' => 'marcela.rivas@test.com', 'genero' => 'F', 'telefono' => '77711172'],
    ['nombre' => 'Arturo', 'apellido' => 'Salas', 'ci' => '1000073', 'email' => 'arturo.salas@test.com', 'genero' => 'M', 'telefono' => '77711173'],
    ['nombre' => 'Rocio', 'apellido' => 'Contreras', 'ci' => '1000074', 'email' => 'rocio.contreras@test.com', 'genero' => 'F', 'telefono' => '77711174'],
    ['nombre' => 'Guillermo', 'apellido' => 'Bustos', 'ci' => '1000075', 'email' => 'guillermo.bustos@test.com', 'genero' => 'M', 'telefono' => '77711175'],

    // Cuarto bloque - Biología (15 estudiantes)
    ['nombre' => 'Vanessa', 'apellido' => 'Paz', 'ci' => '1000076', 'email' => 'vanessa.paz@test.com', 'genero' => 'F', 'telefono' => '77711176'],
    ['nombre' => 'Omar', 'apellido' => 'Cortez', 'ci' => '1000077', 'email' => 'omar.cortez@test.com', 'genero' => 'M', 'telefono' => '77711177'],
    ['nombre' => 'Gabriela', 'apellido' => 'Leon', 'ci' => '1000078', 'email' => 'gabriela.leon@test.com', 'genero' => 'F', 'telefono' => '77711178'],
    ['nombre' => 'Nicolas', 'apellido' => 'Marquez', 'ci' => '1000079', 'email' => 'nicolas.marquez@test.com', 'genero' => 'M', 'telefono' => '77711179'],
    ['nombre' => 'Alejandra', 'apellido' => 'Cisneros', 'ci' => '1000080', 'email' => 'alejandra.cisneros@test.com', 'genero' => 'F', 'telefono' => '77711180'],
    ['nombre' => 'Hernan', 'apellido' => 'Valencia', 'ci' => '1000081', 'email' => 'hernan.valencia@test.com', 'genero' => 'M', 'telefono' => '77711181'],
    ['nombre' => 'Susana', 'apellido' => 'Rios', 'ci' => '1000082', 'email' => 'susana.rios@test.com', 'genero' => 'F', 'telefono' => '77711182'],
    ['nombre' => 'Federico', 'apellido' => 'Galindo', 'ci' => '1000083', 'email' => 'federico.galindo@test.com', 'genero' => 'M', 'telefono' => '77711183'],
    ['nombre' => 'Liliana', 'apellido' => 'Mora', 'ci' => '1000084', 'email' => 'liliana.mora@test.com', 'genero' => 'F', 'telefono' => '77711184'],
    ['nombre' => 'Renato', 'apellido' => 'Carrillo', 'ci' => '1000085', 'email' => 'renato.carrillo@test.com', 'genero' => 'M', 'telefono' => '77711185'],
    ['nombre' => 'Katherine', 'apellido' => 'Villalba', 'ci' => '1000086', 'email' => 'katherine.villalba@test.com', 'genero' => 'F', 'telefono' => '77711186'],
    ['nombre' => 'Sebastian', 'apellido' => 'Barrios', 'ci' => '1000087', 'email' => 'sebastian.barrios@test.com', 'genero' => 'M', 'telefono' => '77711187'],
    ['nombre' => 'Daniela', 'apellido' => 'Montes', 'ci' => '1000088', 'email' => 'daniela.montes@test.com', 'genero' => 'F', 'telefono' => '77711188'],
    ['nombre' => 'Cristian', 'apellido' => 'Rangel', 'ci' => '1000089', 'email' => 'cristian.rangel@test.com', 'genero' => 'M', 'telefono' => '77711189'],
    ['nombre' => 'Yesica', 'apellido' => 'Soliz', 'ci' => '1000090', 'email' => 'yesica.soliz@test.com', 'genero' => 'F', 'telefono' => '77711190'],

    //Astronomía
    ['nombre' => 'Walter', 'apellido' => 'Miranda', 'ci' => '1000091', 'email' => 'walter.miranda@test.com', 'genero' => 'M', 'telefono' => '77711191'],
    ['nombre' => 'Ximena', 'apellido' => 'Franco', 'ci' => '1000092', 'email' => 'ximena.franco@test.com', 'genero' => 'F', 'telefono' => '77711192'],
    ['nombre' => 'Ivan', 'apellido' => 'Paredes', 'ci' => '1000093', 'email' => 'ivan.paredes@test.com', 'genero' => 'M', 'telefono' => '77711193'],
    ['nombre' => 'Regina', 'apellido' => 'Salvatierra', 'ci' => '1000094', 'email' => 'regina.salvatierra@test.com', 'genero' => 'F', 'telefono' => '77711194'],
    ['nombre' => 'Edgar', 'apellido' => 'Campos', 'ci' => '1000095', 'email' => 'edgar.campos@test.com', 'genero' => 'M', 'telefono' => '77711195'],
    ['nombre' => 'Fabiola', 'apellido' => 'Arce', 'ci' => '1000096', 'email' => 'fabiola.arce@test.com', 'genero' => 'F', 'telefono' => '77711196'],
    ['nombre' => 'Milton', 'apellido' => 'Villca', 'ci' => '1000097', 'email' => 'milton.villca@test.com', 'genero' => 'M', 'telefono' => '77711197'],
    ['nombre' => 'Doris', 'apellido' => 'Mamani', 'ci' => '1000098', 'email' => 'doris.mamani@test.com', 'genero' => 'F', 'telefono' => '77711198'],
    ['nombre' => 'Rolando', 'apellido' => 'Quispe', 'ci' => '1000099', 'email' => 'rolando.quispe@test.com', 'genero' => 'M', 'telefono' => '77711199'],
    ['nombre' => 'Nancy', 'apellido' => 'Yujra', 'ci' => '1000100', 'email' => 'nancy.yujra@test.com', 'genero' => 'F', 'telefono' => '77711200'],
];
        

            $personasCompetidores = [];
            foreach ($personasCompetidoresData as $data) {
                $personasCompetidores[] = Persona::create($data);
            }

            // 9. Crear Instituciones
            $institucion1 = Institucion::create(['nombre' => 'Colegio Don Bosco']);
            $institucion2 = Institucion::create(['nombre' => 'Colegio La Salle']);

            // 10. Crear AreaNivel para las Areas con más relaciones
            $areaMatematicas = $areas->firstWhere('nombre', 'Matemáticas');
            $areaFisica = $areas->firstWhere('nombre', 'Física');
            $areaQuimica = $areas->firstWhere('nombre', 'Química');
            $areaBiologia = $areas->firstWhere('nombre', 'Biología');
            $areaInformatica = $areas->firstWhere('nombre', 'Informática');
            $areaArte = $areas->firstWhere('nombre', 'Arte');

            $areaNivelesMatematicas = [];
            $areaNivelesFisica = [];
            $areaNivelesQuimica = [];
            $areaNivelesBiologia = [];
            $areaNivelesInformatica = [];
            $areaNivelesArte = [];

            // Grados disponibles para asignar aleatoriamente
            $gradosBasica = [$grado1ro, $grado2do, $grado3ro, $grado4to, $grado5to, $grado6to];
            $gradosSecundaria = [$grado7mo, $grado8vo, $grado9no, $grado10mo, $grado11vo, $grado12vo];

            // Crear AreaNivel para Matemáticas (3 niveles con grados aleatorios)
            foreach ($niveles->take(3) as $nivel) {
                $gradoAleatorio = $gradosSecundaria[array_rand($gradosSecundaria)];
                $areaNivelesMatematicas[$nivel->id_nivel] = AreaNivel::create([
                    'id_area' => $areaMatematicas->id_area,
                    'id_nivel' => $nivel->id_nivel,
                    'id_grado_escolaridad' => $gradoAleatorio->id_grado_escolaridad,
                    'id_olimpiada' => $olimpiada->id_olimpiada,
                    'activo' => true,
                ]);
            }

            // Crear AreaNivel para Física (3 niveles con grados aleatorios)
            foreach ($niveles->take(3) as $nivel) {
                $gradoAleatorio = $gradosSecundaria[array_rand($gradosSecundaria)];
                $areaNivelesFisica[$nivel->id_nivel] = AreaNivel::create([
                    'id_area' => $areaFisica->id_area,
                    'id_nivel' => $nivel->id_nivel,
                    'id_grado_escolaridad' => $gradoAleatorio->id_grado_escolaridad,
                    'id_olimpiada' => $olimpiada->id_olimpiada,
                    'activo' => true,
                ]);
            }

            // Crear AreaNivel para Arte (3 niveles con grados aleatorios)
            foreach ($niveles->take(3) as $nivel) {
                $gradoAleatorio = $gradosSecundaria[array_rand($gradosSecundaria)];
                $areaNivelesArte[$nivel->id_nivel] = AreaNivel::create([
                    'id_area' => $areaArte->id_area,
                    'id_nivel' => $nivel->id_nivel,
                    'id_grado_escolaridad' => $gradoAleatorio->id_grado_escolaridad,
                    'id_olimpiada' => $olimpiada->id_olimpiada,
                    'activo' => true,
                ]);
            }

            // Crear AreaNivel para Química (3 niveles con grados aleatorios)
            foreach ($niveles->take(3) as $nivel) {
                $gradoAleatorio = $gradosSecundaria[array_rand($gradosSecundaria)];
                $areaNivelesQuimica[$nivel->id_nivel] = AreaNivel::create([
                    'id_area' => $areaQuimica->id_area,
                    'id_nivel' => $nivel->id_nivel,
                    'id_grado_escolaridad' => $gradoAleatorio->id_grado_escolaridad,
                    'id_olimpiada' => $olimpiada->id_olimpiada,
                    'activo' => true,
                ]);
            }

            // Crear AreaNivel para Biología (3 niveles con grados aleatorios)
            foreach ($niveles->take(3) as $nivel) {
                $gradoAleatorio = $gradosSecundaria[array_rand($gradosSecundaria)];
                $areaNivelesBiologia[$nivel->id_nivel] = AreaNivel::create([
                    'id_area' => $areaBiologia->id_area,
                    'id_nivel' => $nivel->id_nivel,
                    'id_grado_escolaridad' => $gradoAleatorio->id_grado_escolaridad,
                    'id_olimpiada' => $olimpiada->id_olimpiada,
                    'activo' => true,
                ]);
            }

            // Crear AreaNivel para Informática (3 niveles con grados aleatorios)
            foreach ($niveles->take(3) as $nivel) {
                $gradoAleatorio = $gradosSecundaria[array_rand($gradosSecundaria)];
                $areaNivelesInformatica[$nivel->id_nivel] = AreaNivel::create([
                    'id_area' => $areaInformatica->id_area,
                    'id_nivel' => $nivel->id_nivel,
                    'id_grado_escolaridad' => $gradoAleatorio->id_grado_escolaridad,
                    'id_olimpiada' => $olimpiada->id_olimpiada,
                    'activo' => true,
                ]);
            }

            $this->command->info('AreaNivel creados para todas las áreas con 3 niveles cada una.');

            // 10.1 Asignar el evaluador a Matemáticas
            $primerAreaNivelMat = reset($areaNivelesMatematicas);
            $evaluadorAn = EvaluadorAn::create([
                'id_usuario' => $evaluadorUser->id_usuario,
                'id_area_nivel' => $primerAreaNivelMat->id_area_nivel,
            ]);

            $this->command->info('Usuarios asignados como responsables de área y evaluadores.');

            // 11. Crear Fases y Parámetros para Matemáticas (primer nivel)
            $faseClasificatoria = Fase::create([
                'nombre' => 'Clasificatoria',
                'orden' => 1,
                'id_area_nivel' => $primerAreaNivelMat->id_area_nivel
            ]);
            
            $faseFinal = Fase::create([
                'nombre' => 'Final',
                'orden' => 2,
                'id_area_nivel' => $primerAreaNivelMat->id_area_nivel
            ]);

            $parametro = Parametro::create([
                'nota_min_clasif' => 51,
                'cantidad_max_apro' => 10,
                'id_area_nivel' => $primerAreaNivelMat->id_area_nivel
            ]);

            $this->command->info('Fases y parámetros creados.');

            // 12. Crear Competidores para diferentes áreas
            $competidores = [];

            // Competidores para Matemáticas
            foreach (array_slice($personasCompetidores, 0, 6) as $index => $persona) {
                $areaNivel = $areaNivelesMatematicas[array_rand($areaNivelesMatematicas)];
                $competidores[] = Competidor::create([
                    'departamento' => $index % 2 == 0 ? 'La Paz' : 'Cochabamba',
                    'contacto_tutor' => '7772222' . $index,
                    'id_institucion' => $index % 2 == 0 ? $institucion1->id_institucion : $institucion2->id_institucion,
                    'id_persona' => $persona->id_persona,
                    'id_grado_escolaridad' => $areaNivel->id_grado_escolaridad,
                    'id_area_nivel' => $areaNivel->id_area_nivel
                ]);
            }

            // Competidores para Física
            foreach (array_slice($personasCompetidores, 6, 4) as $index => $persona) {
                $areaNivel = $areaNivelesFisica[array_rand($areaNivelesFisica)];
                $competidores[] = Competidor::create([
                    'departamento' => 'La Paz',
                    'contacto_tutor' => '7773333' . $index,
                    'id_institucion' => $institucion1->id_institucion,
                    'id_persona' => $persona->id_persona,
                    'id_grado_escolaridad' => $areaNivel->id_grado_escolaridad,
                    'id_area_nivel' => $areaNivel->id_area_nivel
                ]);
            }

            $this->command->info('Competidores creados para diferentes áreas.');

            // 13. Crear una Competencia (evento final) - CORREGIDO: eliminar id_parametro
            $competencia = Competencia::create([
                'fecha_inicio' => '2023-11-01',
                'fecha_fin' => '2023-11-02',
                'estado' => 'Finalizado',
                'id_fase' => $faseFinal->id_fase,
                'id_responsableArea' => $responsableArea->id_responsableArea,
            ]);
            $this->command->info('Registro de Competencia creado.');

            // 14. Crear Evaluaciones y asociarlas a la competencia - CORREGIDO: agregar id_evaluadorAN
            $evaluacionesData = [
                ['nota' => 95.50, 'estado' => 'Calificado', 'id_competidor' => $competidores[0]->id_competidor],
                ['nota' => 88.00, 'estado' => 'Calificado', 'id_competidor' => $competidores[1]->id_competidor],
                ['nota' => 76.50, 'estado' => 'Calificado', 'id_competidor' => $competidores[2]->id_competidor],
                ['nota' => 45.00, 'estado' => 'Calificado', 'id_competidor' => $competidores[3]->id_competidor],
            ];
            
            foreach ($evaluacionesData as $data) {
                Evaluacion::create([
                    'nota' => $data['nota'],
                    'fecha_evaluacion' => '2023-10-15',
                    'estado' => $data['estado'],
                    'id_competidor' => $data['id_competidor'],
                    'id_competencia' => $competencia->id_competencia,
                    'id_evaluadorAN' => $evaluadorAn->id_evaluadorAN,
                ]);
            }
            $this->command->info('Evaluaciones creadas y asociadas a la competencia.');

            // 15. Crear Grupos y asignar competidores clasificados - CORREGIDO: usar tabla pivote
            $grupoFinal = Grupo::create([
                'nombre' => 'Grupo Finalistas', 
            ]);
            
            // Asignar competidores al grupo usando la tabla pivote
            DB::table('grupo_competidor')->insert([
                ['id_grupo' => $grupoFinal->id_grupo, 'id_competidor' => $competidores[0]->id_competidor],
                ['id_grupo' => $grupoFinal->id_grupo, 'id_competidor' => $competidores[1]->id_competidor],
                ['id_grupo' => $grupoFinal->id_grupo, 'id_competidor' => $competidores[2]->id_competidor],
            ]);
            $this->command->info('Grupos y asignación de competidores finalistas creados.');

            // 16. Crear Medallero
            Medallero::create([
                'puesto' => 1, 
                'medalla' => 'Oro', 
                'id_competidor' => $competidores[0]->id_competidor, 
                'id_competencia' => $competencia->id_competencia
            ]);
            Medallero::create([
                'puesto' => 2, 
                'medalla' => 'Plata', 
                'id_competidor' => $competidores[1]->id_competidor, 
                'id_competencia' => $competencia->id_competencia
            ]);
            Medallero::create([
                'puesto' => 3, 
                'medalla' => 'Bronce', 
                'id_competidor' => $competidores[2]->id_competidor, 
                'id_competencia' => $competencia->id_competencia
            ]);
            $this->command->info('Medallero generado.');

            // 17. Simular una desclasificación
            $competidorDescalificado = Competidor::create([
                'departamento' => 'La Paz',
                'contacto_tutor' => '77722229',
                'id_institucion' => $institucion1->id_institucion,
                'id_area_nivel' => $primerAreaNivelMat->id_area_nivel,
                'id_persona' => $personasCompetidores[7]->id_persona,
                'id_grado_escolaridad' => $primerAreaNivelMat->id_grado_escolaridad
            ]);

            $evaluacionDescalificada = Evaluacion::create([
                'nota' => 0, 
                'fecha_evaluacion' => '2023-10-15', 
                'estado' => 'Calificado',
                'id_competidor' => $competidorDescalificado->id_competidor,
                'id_competencia' => $competencia->id_competencia,
                'id_evaluadorAN' => $evaluadorAn->id_evaluadorAN,
            ]);
            
            Desclasificacion::create([
                'fecha' => '2023-10-16',
                'motivo' => 'Se detectó plagio durante la prueba.',
                'id_competidor' => $competidorDescalificado->id_competidor,
                'id_evaluacion' => $evaluacionDescalificada->id_evaluacion,
            ]);
            $this->command->info('Ejemplo de desclasificación creado.');

            // 18. Crear un Aval
            Aval::create([
                'fecha_aval' => '2023-11-05',
                'estado' => 'Aprobado',
                'id_competencia' => $competencia->id_competencia,
                'id_fase' => $faseFinal->id_fase,
                'id_responsableArea' => $responsableArea->id_responsableArea,
            ]);
            $this->command->info('Aval de resultados creado.');

            $this->command->info('¡Seeder de Olimpiada 2021 completado exitosamente!');
        });
    }
}