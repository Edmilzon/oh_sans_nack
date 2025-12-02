<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ReporteRepository
{
    /**
     * Obtiene el historial de cambios con filtros avanzados.
     */
    public function getHistorialCambios(int $limit, ?int $idArea, ?array $idsNiveles, ?string $search = null): LengthAwarePaginator
    {
        $query = DB::table('log_cambio_nota as log')
            ->join('evaluacion as ev', 'log.id_evaluacion', '=', 'ev.id_evaluacion')
            ->join('competidor as comp', 'ev.id_competidor', '=', 'comp.id_competidor')
            ->join('persona as p_olimp', 'comp.id_persona', '=', 'p_olimp.id_persona')
            ->join('evaluador_an as ea', 'ev.id_evaluador_an', '=', 'ea.id_evaluador_an')
            ->join('usuario as u', 'ea.id_usuario', '=', 'u.id_usuario')
            ->join('persona as p_eval', 'u.id_persona', '=', 'p_eval.id_persona')
            ->join('competencia as c', 'ev.id_competencia', '=', 'c.id_competencia')
            ->join('area_nivel as an', 'c.id_area_nivel', '=', 'an.id_area_nivel')
            ->join('nivel as n', 'an.id_nivel', '=', 'n.id_nivel')
            ->join('area_olimpiada as ao', 'an.id_area_olimpiada', '=', 'ao.id_area_olimpiada')
            ->join('area as a', 'ao.id_area', '=', 'a.id_area')
            // JOIN EXTRA para asegurar contexto de Olimpiada (opcional pero recomendado para consistencia)
            ->join('olimpiada as o', 'ao.id_olimpiada', '=', 'o.id_olimpiada')

            ->select(
                'log.id_log_cambio_nota as id_historial',
                'log.fecha_cambio as fecha_hora',
                'log.nota_anterior',
                'log.nota_nueva',
                'ev.observacion as observacion_actual',
                'p_olimp.nombre as nom_olimp',
                'p_olimp.apellido as ape_olimp',
                'p_eval.nombre as nom_eval',
                'p_eval.apellido as ape_eval',
                'a.id_area',
                'a.nombre as nombre_area',
                'n.id_nivel',
                'n.nombre as nombre_nivel',
                'o.gestion' // Útil para saber de qué gestión es el cambio
            );

        // Si se requiere filtrar SOLO por la gestión activa en el historial, descomenta esto:
        // $query->where('o.estado', true);
        // Nota: Generalmente el historial histórico se deja abierto, pero los combos sí se filtran.

        if ($idArea) {
            $query->where('a.id_area', $idArea);
        }

        if (!empty($idsNiveles)) {
            $query->whereIn('n.id_nivel', $idsNiveles);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('p_olimp.nombre', 'like', "%{$search}%")
                  ->orWhere('p_olimp.apellido', 'like', "%{$search}%")
                  ->orWhere('p_eval.nombre', 'like', "%{$search}%");
            });
        }

        $query->orderBy('log.fecha_cambio', 'desc');

        return $query->paginate($limit);
    }

    /**
     * CORREGIDO: Obtiene solo áreas de la OLIMPIADA ACTIVA.
     */
    public function getAreasSimples(): Collection
    {
        return DB::table('area as a')
            ->join('area_olimpiada as ao', 'a.id_area', '=', 'ao.id_area')
            ->join('olimpiada as o', 'ao.id_olimpiada', '=', 'o.id_olimpiada')
            ->where('o.estado', true) // <--- FILTRO CLAVE: Solo gestión actual
            ->select('a.id_area', 'a.nombre')
            ->distinct() // Evita duplicados si hubiera mala configuración
            ->orderBy('a.nombre')
            ->get();
    }

    /**
     * Obtiene niveles por área FILTRANDO POR OLIMPIADA ACTIVA.
     */
    public function getNivelesPorArea(int $idArea): Collection
    {
        return DB::table('area_nivel as an')
            ->join('area_olimpiada as ao', 'an.id_area_olimpiada', '=', 'ao.id_area_olimpiada')
            ->join('nivel as n', 'an.id_nivel', '=', 'n.id_nivel')
            ->join('olimpiada as o', 'ao.id_olimpiada', '=', 'o.id_olimpiada')
            ->where('ao.id_area', $idArea)
            ->where('o.estado', true) // <--- FILTRO CLAVE: Solo gestión actual
            ->select('n.id_nivel', 'n.nombre')
            ->distinct()
            ->orderBy('n.nombre')
            ->get();
    }
}
