<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class UsuarioAccionesService
{
    public function obtenerAccionesCombinadas(int $idUsuario, int $idFaseGlobal, int $idOlimpiada): Collection
    {

        $rolesIds = DB::table('usuario_rol')
            ->where('id_usuario', $idUsuario)
            ->where('id_olimpiada', $idOlimpiada)
            ->pluck('id_rol')
            ->toArray();

        if (empty($rolesIds)) {
            return collect([]);
        }

        return DB::table('accion_sistema as a')
            ->join('rol_accion as ra', 'a.id_accion', '=', 'ra.id_accion')
            ->join('configuracion_accion as ca', 'a.id_accion', '=', 'ca.id_accion')
            ->whereIn('ra.id_rol', $rolesIds)
            ->where('ra.activo', true)
            ->where('ca.id_fase_global', $idFaseGlobal)
            ->where('ca.id_olimpiada', $idOlimpiada)
            ->where('ca.habilitada', true)
            ->select([
                'a.id_accion',
                'a.codigo',
                'a.nombre',
                'a.descripcion'
            ])
            ->distinct()
            ->get();
    }
}
