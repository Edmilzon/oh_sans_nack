<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class AccionDisponibilidadRepository
{
    /**
     * Obtiene las acciones cruzando:
     * 1. Permisos del Rol (tabla rol_acciones)
     * 2. Configuración de la Fase (tabla configuracion_accion)
     */
    public function obtenerAccionesHabilitadas(int $idRol, int $idFaseGlobal, int $idOlimpiada): Collection
    {
        return DB::table('accion_sistema as a')
            // 1. Unimos con los permisos del Rol
            ->join('rol_accion as ra', 'a.id_accion', '=', 'ra.id_accion')
            // 2. Unimos con la configuración de la Fase/Olimpiada
            ->join('configuracion_accion as ca', 'a.id_accion', '=', 'ca.id_accion')
            // 3. Filtros
            ->where('ra.id_rol', $idRol)
            ->where('ra.activo', true) // Solo si la asignación al rol está activa
            ->where('ca.id_fase_global', $idFaseGlobal)
            ->where('ca.id_olimpiada', $idOlimpiada) // Mapeo de gestión a olimpiada
            ->where('ca.habilitada', true) // Solo si está habilitada en esta fase
            // 4. Selección de columnas limpias
            ->select([
                'a.id_accion',
                'a.codigo',
                'a.nombre',
                'a.descripcion'
            ])
            ->distinct() // Por seguridad, para evitar duplicados si hubiera mala data
            ->get();
    }
}
