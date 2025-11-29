<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\Usuario;
use App\Model\EvaluadorAn;
use App\Model\AreaNivel;
use App\Model\Rol;
use App\Model\Olimpiada;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class EvaluadorSeeder extends Seeder
{
    public function run(): void
    {
        $idAreaNivel = 11;
        $password = 'password123';
        $this->command->info("--- Iniciando EvaluadorSeeder para AreaNivel ID: {$idAreaNivel} ---");

        $areaNivel = AreaNivel::find($idAreaNivel);

        if (!$areaNivel) {
            $this->command->error("No se encontró el AreaNivel con ID {$idAreaNivel}. No se crearon evaluadores.");
            return;
        }

        $areaNivel->load('area', 'nivel', 'olimpiada');
        $nombreArea = $areaNivel->area->nombre;
        $nombreNivel = $areaNivel->nivel->nombre;
        $olimpiada = $areaNivel->olimpiada;

        if (!$olimpiada) {
            $this->command->error("El AreaNivel ID {$idAreaNivel} no está asociado a ninguna olimpiada.");
            return;
        }

        $rolEvaluador = Rol::where('nombre', 'Evaluador')->first();
        if (!$rolEvaluador) {
            $this->command->error('El rol "Evaluador" no existe. Ejecuta RolesSeeder primero.');
            return;
        }

        $creados = 0;
        for ($i = 1; $i <= 5; $i++) {
            $ci = "1111{$idAreaNivel}{$i}"; // CI predecible y único
            $email = "evaluador_an{$idAreaNivel}_{$i}@ohsansi.com"; // Email predecible y único

            // Verificar si el usuario ya existe por CI o email
            $usuarioExistente = Usuario::where('ci', $ci)->orWhere('email', $email)->first();

            if ($usuarioExistente) {
                $this->command->warn("Usuario con CI {$ci} o email {$email} ya existe. Verificando si es evaluador para este AreaNivel...");
                
                $esEvaluador = EvaluadorAn::where('id_usuario', $usuarioExistente->id_usuario)
                                          ->where('id_area_nivel', $idAreaNivel)
                                          ->exists();
                if ($esEvaluador) {
                    $this->command->line("-> El usuario ya es evaluador para {$nombreArea} - {$nombreNivel}. Saltando.");
                    continue;
                }
            }

            // Usar transacción para garantizar consistencia
            DB::transaction(function () use ($ci, $email, $password, $rolEvaluador, $olimpiada, $idAreaNivel, $i, &$creados, $nombreArea, $nombreNivel) {
                $usuario = Usuario::create([
                    'nombre' => "Evaluador Adicional " . $i,
                    'apellido' => "AN-{$idAreaNivel}",
                    'ci' => $ci,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'telefono' => "6000000{$i}",
                ]);

                // Asignar rol de evaluador
                DB::table('usuario_rol')->insert([
                    'id_usuario' => $usuario->id_usuario,
                    'id_rol' => $rolEvaluador->id_rol,
                    'id_olimpiada' => $olimpiada->id_olimpiada,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Asignar al area_nivel
                EvaluadorAn::create([
                    'id_usuario' => $usuario->id_usuario,
                    'id_area_nivel' => $idAreaNivel,
                ]);
                
                $creados++;
                $this->command->info("✔️ Creado evaluador {$i}/5 para {$nombreArea} - {$nombreNivel} (CI: {$ci})");
            });
        }

        if ($creados > 0) {
            $this->command->info("✅ Se crearon {$creados} nuevos evaluadores para {$nombreArea} - {$nombreNivel} con la contraseña '{$password}'.");
        } else {
            $this->command->info("No se crearon nuevos evaluadores. Es posible que ya existieran todos.");
        }
        $this->command->info("--- Finalizado EvaluadorSeeder ---");
    }
}
