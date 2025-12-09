<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class AccionDisponibilidadRepository
{

    public function obtenerAccionesHabilitadas(int $idRol, int $idFaseGlobal, int $idOlimpiada): Collection
    {
        return DB::table('accion_sistema as a')
            ->join('rol_accion as ra', 'a.id_accion', '=', 'ra.id_accion')
            ->join('configuracion_accion as ca', 'a.id_accion', '=', 'ca.id_accion')
            ->where('ra.id_rol', $idRol)
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
