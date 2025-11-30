<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\Rol;
use App\Model\AccionSistema;
use App\Model\RolAccion;

class RolAccionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Obtener Roles
        $admin = Rol::where('nombre_rol', 'Administrador')->first();
        $responsable = Rol::where('nombre_rol', 'Responsable Area')->first();
        $evaluador = Rol::where('nombre_rol', 'Evaluador')->first();

        if (!$admin) { $this->command->error('Faltan roles. Ejecuta RolesSeeder.'); return; }

        // 2. Mapeo de Permisos (Código de Acción -> Roles que la tienen)
        $permisos = [
            // Admin tiene TODO (Lo haremos dinámico abajo o manual)
            'CREAR_USUARIO'   => [$admin, $responsable],
            'ASIGNAR_ROL'     => [$admin],
            'CONF_CRONOGRAMA' => [$admin],
            
            // Responsable
            'INSCRIP_EST'     => [$admin, $responsable],
            'IMP_CSV_EST'     => [$admin, $responsable],
            'CREAR_COMP'      => [$admin, $responsable],
            'VER_REP_NOTAS'   => [$admin, $responsable, $evaluador], // Todos ven notas
            
            // Evaluador
            'REG_NOTA'        => [$evaluador],
        ];

        $accionesDB = AccionSistema::all()->keyBy('codigo_acc_sis');

        foreach ($permisos as $codigo => $rolesAsignados) {
            if (isset($accionesDB[$codigo])) {
                $accion = $accionesDB[$codigo];
                
                foreach ($rolesAsignados as $rol) {
                    RolAccion::firstOrCreate([
                        'id_rol' => $rol->id_rol,
                        'id_accion' => $accion->id_accion
                    ], ['activo' => true]);
                }
            }
        }

        $this->command->info('✅ Permisos por defecto (RolAccion) asignados correctamente.');
    }
}