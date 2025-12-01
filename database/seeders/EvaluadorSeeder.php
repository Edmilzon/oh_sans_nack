<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Model\Usuario;
use App\Model\Persona;
use App\Model\EvaluadorAn;
use App\Model\AreaNivel;
use App\Model\Rol;
use App\Model\Olimpiada;

class EvaluadorSeeder extends Seeder
{
    public function run(): void
    {
        $passwordDefault = 'password123';

        $olimpiada = Olimpiada::where('gestion', date('Y'))->first()
                     ?? Olimpiada::latest('id_olimpiada')->first();

        if (!$olimpiada) {
            $this->command->error('❌ No se encontró una olimpiada activa.');
            return;
        }

        // CORRECCIÓN: Buscar AreaNivel a través de AreaOlimpiada
        // Filtramos area_nivel que pertenezca a un area_olimpiada de ESTA olimpiada
        $areaNivel = AreaNivel::whereHas('areaOlimpiada', function($q) use ($olimpiada) {
            $q->where('id_olimpiada', $olimpiada->id_olimpiada);
        })->with(['areaOlimpiada.area', 'nivel'])->first();

        if (!$areaNivel) {
            $this->command->error("⚠️ No hay niveles configurados para la olimpiada '{$olimpiada->nombre}'. Ejecuta AreasEvaluadoresSeeder o similar primero.");
            return;
        }

        // Acceder a los datos a través de la relación correcta
        $nombreArea = $areaNivel->areaOlimpiada->area->nombre;
        $nombreNivel = $areaNivel->nivel->nombre;
        $idAreaNivel = $areaNivel->id_area_nivel;

        $this->command->info("--- Configurando evaluadores para: {$nombreArea} - {$nombreNivel} ---");

        $rolEvaluador = Rol::where('nombre', 'Evaluador')->first();
        if (!$rolEvaluador) {
            $this->command->error('❌ El rol "Evaluador" no existe.');
            return;
        }

        $creados = 0;

        DB::transaction(function () use ($idAreaNivel, $passwordDefault, $rolEvaluador, $olimpiada, &$creados, $nombreArea) {
            for ($i = 1; $i <= 5; $i++) {
                $ci = "EVAL-{$idAreaNivel}-{$i}";
                $email = strtolower("eval.{$i}.{$nombreArea}@ohsansi.com");
                $email = str_replace(' ', '', $email);

                // Crear Persona (Sin Genero)
                $persona = Persona::firstOrCreate(
                    ['ci' => $ci],
                    [
                        'nombre'   => "Evaluador {$i}",
                        'apellido' => "Nivel {$idAreaNivel}",
                        'email'    => "personal.{$email}",
                        'telefono' => "6000000{$i}"
                        // 'genero' => ... ELIMINADO: No existe en tabla persona
                    ]
                );

                // Crear Usuario
                $usuario = Usuario::firstOrCreate(
                    ['email' => $email],
                    [
                        'id_persona' => $persona->id_persona,
                        'password'   => $passwordDefault
                    ]
                );

                // Asignar Rol
                if (!$usuario->roles()
                        ->where('rol.id_rol', $rolEvaluador->id_rol)
                        ->wherePivot('id_olimpiada', $olimpiada->id_olimpiada)
                        ->exists()) {

                    $usuario->roles()->attach($rolEvaluador->id_rol, [
                        'id_olimpiada' => $olimpiada->id_olimpiada
                    ]);
                }

                // Asignar Permiso
                EvaluadorAn::firstOrCreate([
                    'id_usuario'    => $usuario->id_usuario,
                    'id_area_nivel' => $idAreaNivel
                ], [
                    'estado' => true
                ]);

                $creados++;
            }
        });

        $this->command->info("✅ Se aseguraron {$creados} evaluadores extra.");
    }
}
