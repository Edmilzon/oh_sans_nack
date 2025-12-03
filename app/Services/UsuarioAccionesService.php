<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class UsuarioAccionesService
{
    /**
     * Obtiene la unión de todas las acciones permitidas para todos los roles
     * que tenga el usuario en esa gestión específica.
     */
    public function obtenerAccionesCombinadas(int $idUsuario, int $idFaseGlobal, int $idOlimpiada): Collection
    {
        // 1. Obtener todos los IDs de roles que el usuario tiene EN ESTA OLIMPIADA
        // Nota: Usamos la tabla pivote 'usuario_rol' que tiene el campo 'id_olimpiada'
        $rolesIds = DB::table('usuario_rol')
            ->where('id_usuario', $idUsuario)
            ->where('id_olimpiada', $idOlimpiada)
            ->pluck('id_rol')
            ->toArray();

        if (empty($rolesIds)) {
            return collect([]); // El usuario no tiene roles en esta gestión
        }

        // 2. Buscar acciones habilitadas para CUALQUIERA de esos roles
        // Usamos 'whereIn' para la magia de múltiples roles
        return DB::table('accion_sistema as a')
            ->join('rol_accion as ra', 'a.id_accion', '=', 'ra.id_accion')
            ->join('configuracion_accion as ca', 'a.id_accion', '=', 'ca.id_accion')
            ->whereIn('ra.id_rol', $rolesIds) // <--- AQUÍ ESTÁ EL CAMBIO CLAVE
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
            ->distinct() // Evita duplicados si dos roles tienen la misma acción
            ->get();
    }
}
