<?php

namespace App\Repositories;

use App\Model\AreaNivel;
use App\Model\AreaOlimpiada;
use App\Model\Competencia;
use App\Model\Fase;
use App\Model\FaseGlobal;
use App\Model\AccionSistema;
use App\Model\ConfiguracionAccion;
use App\Model\Olimpiada;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FaseRepository
{
    public function obtenerFasesGlobales(): Collection
    {
        return FaseGlobal::orderBy('orden')->get();
    }

    public function obtenerPorAreaNivel(int $id_area_nivel): Collection
    {
        return Fase::where('id_area_nivel', $id_area_nivel)->orderBy('orden')->get();
    }

    public function crearConCompetencia(array $data): Fase
    {
        return DB::transaction(function () use ($data) {
            $fase = Fase::create([
                'nombre' => $data['nombre'],
                'orden' => $data['orden'] ?? 1,
                'id_area_nivel' => $data['id_area_nivel'],
            ]);

            $areaNivel = AreaNivel::findOrFail($data['id_area_nivel']);
            $areaOlimpiada = AreaOlimpiada::where('id_area', $areaNivel->id_area)
                ->where('id_olimpiada', $areaNivel->id_olimpiada)
                ->firstOrFail();

            $responsableArea = DB::table('responsable_area')
                ->where('id_area_olimpiada', $areaOlimpiada->id_area_olimpiada)
                ->first();

            if (!$responsableArea) {
                throw new \Exception("No se encontró un responsable para el área de esta fase.");
            }

            Competencia::create([
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_fin' => $data['fecha_fin'],
                'estado' => $data['estado'],
                'id_fase' => $fase->id_fase,
                'id_responsableArea' => $responsableArea->id_responsableArea,
            ]);

            return $fase->load('competencias');
        });
    }

    public function obtenerPorId(int $id_fase): ?Fase
    {
        return Fase::find($id_fase);
    }

    public function actualizar(int $id_fase, array $data): bool
    {
        $fase = Fase::find($id_fase);
        if ($fase) {
            return $fase->update($data);
        }
        return false;
    }

    public function eliminar(int $id_fase): bool
    {
        $fase = Fase::find($id_fase);
        if ($fase) {
            return $fase->delete();
        }
        return false;
    }

    public function listarAccionesSistema(): Collection
    {
        return AccionSistema::select('id_accion as id', 'codigo', 'nombre')->get();
    }

    public function getConfiguracionAccionesPorGestion(int $idGestion): array
    {
        $olimpiada = Olimpiada::findOrFail($idGestion);
        $fasesGlobales = FaseGlobal::orderBy('orden')->get();
        $accionesSistema = AccionSistema::get();
        $configuraciones = ConfiguracionAccion::where('id_olimpiada', $idGestion)->get();

        $fasesIds = $fasesGlobales->pluck('id_fase_global');
        $accionesIds = $accionesSistema->pluck('id_accion');

        $configuracionMatrix = [];
        foreach ($configuraciones as $config) {
            $configuracionMatrix[$config->id_accion][$config->id_fase_global] = $config->habilitada;
        }

        $accionesResponse = [];
        foreach ($accionesSistema as $accion) {
            $porFase = [];
            foreach ($fasesGlobales as $fase) {
                $habilitada = $configuracionMatrix[$accion->id_accion][$fase->id_fase_global] ?? false;
                $porFase[] = [
                    'idFase' => $fase->id_fase_global,
                    'habilitada' => (bool) $habilitada,
                ];
            }

            $accionesResponse[] = [
                'id' => $accion->id_accion,
                'codigo' => $accion->codigo,
                'nombre' => $accion->nombre,
                'porFase' => $porFase,
            ];
        }

        return [
            'gestion' => [
                'id' => $olimpiada->id_olimpiada,
                'gestion' => $olimpiada->gestion,
            ],
            'fases' => $fasesGlobales->map(function ($fase) {
                return [
                    'id' => $fase->id_fase_global,
                    'codigo' => $fase->codigo,
                    'nombre' => $fase->nombre,
                ];
            }),
            'acciones' => $accionesResponse,
        ];
    }

    public function guardarConfiguracionAccionesPorGestion(int $idGestion, array $accionesPorFase): void
    {
        DB::transaction(function () use ($idGestion, $accionesPorFase) {
            foreach ($accionesPorFase as $accionPorFase) {
                ConfiguracionAccion::updateOrCreate(
                    [
                        'id_olimpiada' => $idGestion,
                        'id_fase_global' => $accionPorFase['idFase'],
                        'id_accion' => $accionPorFase['idAccion'],
                    ],
                    [
                        'habilitada' => $accionPorFase['habilitada'],
                    ]
                );
            }
        });
    }

    public function actualizarAccionHabilitada(int $idGestion, int $idFase, int $idAccion, bool $habilitada): void
    {
        ConfiguracionAccion::updateOrCreate(
            [
                'id_olimpiada' => $idGestion,
                'id_fase_global' => $idFase,
                'id_accion' => $idAccion,
            ],
            [
                'habilitada' => $habilitada,
            ]
        );
    }
    public function getAccionesHabilitadas(int $idGestion, int $idFase)
    {
        return ConfiguracionAccion::where('id_olimpiada', $idGestion)
            ->where('id_fase_global', $idFase)
            ->where('habilitada', true)
            ->join('accion_sistema', 'configuracion_accion.id_accion', '=', 'accion_sistema.id_accion')
            ->pluck('accion_sistema.codigo');
    }

    public function getFaseDetails(int $id_fase): ?array
    {
        $fase = Fase::with('areaNivel.olimpiada')->find($id_fase);

        if (!$fase || $fase->areaNivel->olimpiada->gestion !== '2025') {
            return null;
        }

        $id_area_nivel = $fase->id_area_nivel;

        $cantidad_evaluadores = DB::table('evaluador_an')->where('id_area_nivel', $id_area_nivel)->count();

        $cantidad_competidores = DB::table('competidor')->where('id_area_nivel', $id_area_nivel)->count();

        $competenciaIds = DB::table('competencia')->where('id_fase', $id_fase)->pluck('id_competencia');
        
        $progreso = DB::table('evaluacion')->whereIn('id_competencia', $competenciaIds)->distinct()->count('id_competidor');

        return [
            'cantidad_evaluadores' => $cantidad_evaluadores,
            'cantidad_competidores' => $cantidad_competidores,
            'progreso' => $progreso,
        ];
    }

    public function getSubFasesDetails(int $id_area, int $id_nivel, int $id_olimpiada)
    {
        $areaNivel = AreaNivel::where('id_area', $id_area)
            ->where('id_nivel', $id_nivel)
            ->where('id_olimpiada', $id_olimpiada)
            ->first();

        if (!$areaNivel) {
            return collect();
        }

        $id_area_nivel = $areaNivel->id_area_nivel;

        $fases = Fase::where('id_area_nivel', $id_area_nivel)->orderBy('orden')->get();
        if ($fases->isEmpty()) {
            return collect();
        }

        $cant_evaluadores = DB::table('evaluador_an')->where('id_area_nivel', $id_area_nivel)->count();
        $cant_estudiantes = DB::table('competidor')->where('id_area_nivel', $id_area_nivel)->count();

        $faseIds = $fases->pluck('id_fase');
        $competencias = DB::table('competencia')->whereIn('id_fase', $faseIds)->get();
        $competenciaIds = $competencias->pluck('id_competencia');

        $evaluatedCounts = DB::table('evaluacion')
            ->whereIn('id_competencia', $competenciaIds)
            ->select('id_competencia', DB::raw('COUNT(DISTINCT id_competidor) as count'))
            ->groupBy('id_competencia')
            ->get()
            ->keyBy('id_competencia');

        $competenciasGroupedByFase = $competencias->groupBy('id_fase');

        return $fases->map(function ($fase) use ($cant_estudiantes, $cant_evaluadores, $competenciasGroupedByFase, $evaluatedCounts) {
            $competenciasDeLaFase = $competenciasGroupedByFase->get($fase->id_fase, collect());
            $competidoresEvaluados = 0;
            $estadoCompetencia = 'Pendiente'; 

            if ($competenciasDeLaFase->isNotEmpty()) {
                if ($competenciasDeLaFase->contains('estado', 'En Curso')) {
                    $estadoCompetencia = 'En Curso';
                } elseif ($competenciasDeLaFase->every('estado', 'Finalizado')) {
                    $estadoCompetencia = 'Finalizado';
                }
            }
            
            foreach ($competenciasDeLaFase as $competencia) {
                $competidoresEvaluados += $evaluatedCounts->get($competencia->id_competencia)->count ?? 0;
            }

            $progreso = ($cant_estudiantes > 0) ? ($competidoresEvaluados / $cant_estudiantes) * 100 : 0;
            
            $estadoMapping = [
                'Pendiente' => 'NO_INICIADA',
                'En Curso' => 'EN_EVALUACION',
                'Finalizado' => 'FINALIZADA',
            ];
            $estado = $estadoMapping[$estadoCompetencia] ?? 'NO_INICIADA';

            return [
                'id_subfase' => $fase->id_fase,
                'nombre' => $fase->nombre,
                'orden' => $fase->orden,
                'estado' => $estado,
                'cant_estudiantes' => $cant_estudiantes,
                'cant_evaluadores' => $cant_evaluadores,
                'progreso' => round($progreso),
            ];
        });
    }
}
