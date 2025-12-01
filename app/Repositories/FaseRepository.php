<?php

namespace App\Repositories;

use App\Model\FaseGlobal;
use App\Model\Competencia;
use App\Model\AccionSistema;
use App\Model\ConfiguracionAccion;
use App\Model\Olimpiada;
use App\Model\AreaNivel;
use App\Model\ResponsableArea;
// CORRECCIÓN: Usamos Support\Collection para permitir mapeos
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FaseRepository
{
    // ... (Métodos de obtenerPorAreaNivel, crearCompetencia, etc. se mantienen igual) ...
    // ... (Copia los métodos anteriores que ya funcionaban) ...

    public function obtenerPorAreaNivel(int $idAreaNivel): Collection
    {
        return Competencia::where('id_area_nivel', $idAreaNivel)
            ->orderBy('fecha_inicio')
            ->get()
            ->map(function ($comp) {
                $comp->id_fase = $comp->id_competencia;
                $comp->nombre = $comp->nombre_examen;
                $comp->orden = 0;
                return $comp;
            });
    }

    public function crearCompetencia(array $data, int $idAreaNivel): Competencia
    {
        $areaNivel = AreaNivel::findOrFail($idAreaNivel);
        $idResponsable = null;

        $responsable = ResponsableArea::where('id_area_olimpiada', $areaNivel->id_area_olimpiada)->first();
        if ($responsable && $responsable->usuario) {
            $idResponsable = $responsable->usuario->id_persona;
        }

        $competencia = Competencia::create([
            'id_area_nivel'  => $idAreaNivel,
            'id_persona'     => $idResponsable,
            'nombre_examen'  => $data['nombre'],
            'fecha_inicio'   => $data['fecha_inicio'],
            'fecha_fin'      => $data['fecha_fin'],
            'estado'         => false,
            'id_fase_global' => null,
        ]);

        $competencia->id_fase = $competencia->id_competencia;
        $competencia->nombre = $competencia->nombre_examen;

        return $competencia;
    }

    public function findCompetenciaById(int $id): ?Competencia
    {
        return Competencia::find($id);
    }

    public function updateCompetencia(Competencia $competencia, array $data): bool
    {
        if (isset($data['nombre'])) {
            $data['nombre_examen'] = $data['nombre'];
        }
        return $competencia->update($data);
    }

    public function deleteCompetencia(Competencia $competencia): bool
    {
        return $competencia->delete();
    }

    // --- Métodos Globales (sin cambios) ---
    public function obtenerFasesGlobales(): Collection
    {
        return FaseGlobal::orderBy('orden')->get();
    }

    public function listarAccionesSistema(): Collection
    {
        return AccionSistema::select('id_accion_sistema as id', 'codigo', 'nombre')->get();
    }

    public function getConfiguracionMatriz(int $idOlimpiada): array
    {
        $olimpiada = Olimpiada::findOrFail($idOlimpiada);
        $fasesGlobales = FaseGlobal::where('id_olimpiada', $idOlimpiada)->orderBy('orden')->get();
        if ($fasesGlobales->isEmpty()) $fasesGlobales = FaseGlobal::orderBy('orden')->get();

        $acciones = AccionSistema::all();
        $idsFases = $fasesGlobales->pluck('id_fase_global');
        $configuraciones = ConfiguracionAccion::whereIn('id_fase_global', $idsFases)->get();

        $mapaConfig = [];
        foreach ($configuraciones as $conf) {
            $mapaConfig[$conf->id_accion_sistema][$conf->id_fase_global] = $conf->habilitada;
        }

        $accionesResponse = $acciones->map(function($accion) use ($fasesGlobales, $mapaConfig) {
            $porFase = $fasesGlobales->map(function($fase) use ($accion, $mapaConfig) {
                return [
                    'idFase'     => $fase->id_fase_global,
                    'habilitada' => $mapaConfig[$accion->id_accion_sistema][$fase->id_fase_global] ?? false
                ];
            });
            return [
                'id'      => $accion->id_accion_sistema,
                'codigo'  => $accion->codigo,
                'nombre'  => $accion->nombre,
                'porFase' => $porFase
            ];
        });

        return [
            'gestion' => ['id' => $olimpiada->id_olimpiada, 'gestion' => $olimpiada->gestion],
            'fases' => $fasesGlobales->map(fn($f) => ['id' => $f->id_fase_global, 'codigo' => $f->codigo, 'nombre' => $f->nombre]),
            'acciones' => $accionesResponse
        ];
    }

    public function guardarConfiguracion(array $accionesPorFase): void
    {
        foreach ($accionesPorFase as $item) {
            ConfiguracionAccion::updateOrCreate(
                ['id_accion_sistema' => $item['idAccion'], 'id_fase_global' => $item['idFase']],
                ['habilitada' => $item['habilitada']]
            );
        }
    }

    public function actualizarAccionUnica(int $idFase, int $idAccion, bool $habilitada): void
    {
        ConfiguracionAccion::updateOrCreate(
            ['id_fase_global' => $idFase, 'id_accion_sistema' => $idAccion],
            ['habilitada' => $habilitada]
        );
    }

    // --- NUEVO MÉTODO PARA CAMBIAR ESTADO (PATCH) ---
    public function actualizarEstadoCompetencia(int $idCompetencia, bool $estado): array
    {
        $competencia = Competencia::findOrFail($idCompetencia);
        $competencia->update(['estado' => $estado]);

        return [
            'id_subfase' => $competencia->id_competencia,
            'estado'     => $estado ? 'EN_EVALUACION' : 'FINALIZADA' // O 'NO_INICIADA'
        ];
    }

    // --- REPORTE SUBFASES (FIX DE TIPO DE DATO) ---
    public function getSubFasesDetails(int $idArea, int $idNivel, int $idOlimpiada): Collection
    {
        // 1. Buscar AreaNivel
        $areaNivel = AreaNivel::whereHas('areaOlimpiada', function($q) use ($idArea, $idOlimpiada) {
            $q->where('id_area', $idArea)->where('id_olimpiada', $idOlimpiada);
        })->where('id_nivel', $idNivel)->first();

        if (!$areaNivel) return collect([]);

        $competencias = Competencia::where('id_area_nivel', $areaNivel->id_area_nivel)
            ->orderBy('fecha_inicio')
            ->get();

        // 2. Mapear datos (esto devuelve Support\Collection)
        return $competencias->map(function($comp) use ($areaNivel) {

            $cantEstudiantes = DB::table('competidor')->where('id_area_nivel', $areaNivel->id_area_nivel)->count();
            $cantEvaluadores = DB::table('evaluador_an')->where('id_area_nivel', $areaNivel->id_area_nivel)->where('estado', true)->count();

            $evaluados = DB::table('evaluacion')
                ->where('id_competencia', $comp->id_competencia)
                ->distinct('id_competidor')
                ->count();

            $progreso = ($cantEstudiantes > 0) ? ($evaluados / $cantEstudiantes) * 100 : 0;

            $estadoStr = 'NO_INICIADA';
            if ($comp->estado) $estadoStr = 'EN_EVALUACION';

            return [
                'id_subfase'       => $comp->id_competencia,
                'nombre'           => $comp->nombre_examen,
                'orden'            => 0,
                'estado'           => $estadoStr,
                'cant_estudiantes' => $cantEstudiantes,
                'cant_evaluadores' => $cantEvaluadores,
                'progreso'         => round($progreso)
            ];
        });
    }
}
