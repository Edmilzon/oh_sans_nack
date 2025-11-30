<?php

namespace App\Repositories;

use App\Model\AreaNivel;
use App\Model\AreaOlimpiada;
use App\Model\Competencia;
use App\Model\FaseGlobal;
use App\Model\AccionSistema;
use App\Model\ConfiguracionAccion;
use App\Model\Olimpiada;
use App\Model\ResponsableArea; // Necesario para buscar
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Exception;

class FaseRepository
{
    public function obtenerFasesGlobales(): Collection
    {
        // Columna corregida: orden_fas_glo
        return FaseGlobal::orderBy('orden_fas_glo')->get();
    }

    /**
     * NOTA: El modelo Fase ya no existe.
     * Este método debería listar Competencias asociadas al AreaNivel.
     * Mantenemos la estructura para compatibilidad, listando Competencia.
     */
    public function obtenerPorAreaNivel(int $id_area_nivel): Collection
    {
        return Competencia::where('id_area_nivel', $id_area_nivel)
            ->orderBy('id_fase_global') // Ordenar por fase global para consistencia
            ->get();
    }

    /**
     * NOTA: Este método original creaba una Fase y una Competencia.
     * Ahora solo crea la Competencia.
     * Se mantiene el nombre del método por compatibilidad con el Service/Controller.
     */
    public function crearConCompetencia(array $data): Competencia
    {
        return DB::transaction(function () use ($data) {

            $areaNivel = AreaNivel::with('areaOlimpiada')->findOrFail($data['id_area_nivel']);
            $areaOlimpiada = $areaNivel->areaOlimpiada;

            if (!$areaOlimpiada) {
                throw new Exception("La relación Area-Olimpiada no existe para el id_area_nivel proporcionado.");
            }

            // Buscar el responsable asociado a esa AreaOlimpiada (para determinar la Fase Global, si aplica)
            $responsableArea = ResponsableArea::where('id_area_olimpiada', $areaOlimpiada->id_area_olimpiada)
                ->first();

            if (!$responsableArea) {
                // Esto podría ser un error, o simplemente una advertencia si la lógica de negocio lo permite.
                // Mantengo el throw para reflejar el error original.
                throw new Exception("No se encontró un responsable para el área de esta fase.");
            }

            // Asumo que $data['id_fase_global'] se pasa en el request, ya que Fase ya no existe.
            // Si $data['id_fase_global'] no está disponible, esta lógica fallará.
            $idFaseGlobal = $data['id_fase_global'] ?? FaseGlobal::firstOrFail()->id_fase_global;

            // Crear directamente la Competencia
            $competencia = Competencia::create([
                'id_fase_global' => $idFaseGlobal,
                'id_area_nivel' => $areaNivel->id_area_nivel,
                'nombre_examen' => $data['nombre'],
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_fin' => $data['fecha_fin'],
                // Usamos estado_comp (boolean)
                'estado_comp' => true, // Por defecto activa/iniciada
            ]);

            // Anteriormente se retornaba $fase, ahora retornamos $competencia
            return $competencia;
        });
    }

    /**
     * Reemplazar Fase::find($id_fase) con Competencia::find($id_competencia)
     */
    public function obtenerPorId(int $id_competencia): ?Competencia
    {
        return Competencia::find($id_competencia);
    }

    /**
     * Reemplazar Fase::find($id_fase) con Competencia::find($id_competencia)
     */
    public function actualizar(int $id_competencia, array $data): bool
    {
        $competencia = Competencia::find($id_competencia);
        if ($competencia) {
            return $competencia->update($data);
        }
        return false;
    }

    /**
     * Reemplazar Fase::find($id_fase) con Competencia::find($id_competencia)
     */
    public function eliminar(int $id_competencia): bool
    {
        $competencia = Competencia::find($id_competencia);
        if ($competencia) {
            return $competencia->delete();
        }
        return false;
    }

    public function listarAccionesSistema(): Collection
    {
        // Columnas corregidas: codigo_acc_sis, nombre_acc_sis
        return AccionSistema::select('id_accion as id', 'codigo_acc_sis as codigo', 'nombre_acc_sis as nombre')->get();
    }

    public function getConfiguracionAccionesPorGestion(int $idGestion): array
    {
        $olimpiada = Olimpiada::findOrFail($idGestion);
        // Columna corregida: orden_fas_glo
        $fasesGlobales = FaseGlobal::orderBy('orden_fas_glo')->get();
        $accionesSistema = AccionSistema::get();
        $configuraciones = ConfiguracionAccion::where('id_olimpiada', $idGestion)->get();

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
                'codigo' => $accion->codigo_acc_sis, // Columna corregida
                'nombre' => $accion->nombre_acc_sis, // Columna corregida
                'porFase' => $porFase,
            ];
        }

        return [
            'gestion' => [
                'id' => $olimpiada->id_olimpiada,
                'gestion' => $olimpiada->gestion_olimp, // Columna corregida
            ],
            'fases' => $fasesGlobales->map(function ($fase) {
                return [
                    'id' => $fase->id_fase_global,
                    'codigo' => $fase->codigo_fas_glo, // Columna corregida
                    'nombre' => $fase->nombre_fas_glo, // Columna corregida
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
            // Columna corregida: codigo_acc_sis
            ->pluck('accion_sistema.codigo_acc_sis');
    }

    public function getFaseDetails(int $id_competencia): ?array
    {
        // Rutas de relación corregidas: Competencia -> AreaNivel -> AreaOlimpiada -> Olimpiada
        $competencia = Competencia::with('areaNivel.areaOlimpiada.olimpiada')->find($id_competencia);

        // Columna corregida: gestion_olimp
        if (!$competencia || $competencia->areaNivel->areaOlimpiada->olimpiada->gestion_olimp !== date('Y')) {
            return null;
        }

        $id_area_nivel = $competencia->id_area_nivel;
        $id_fase_global = $competencia->id_fase_global; // Usamos la FK de la competencia

        $cantidad_evaluadores = DB::table('evaluador_an')->where('id_area_nivel', $id_area_nivel)->count();

        // **CORRECCIÓN CRÍTICA:** Contamos competidores A TRAVÉS de la tabla inscripcion.
        $cantidad_competidores = DB::table('inscripcion')
            ->where('id_area_nivel', $id_area_nivel)
            ->distinct()
            ->count('id_competidor');

        // Solo necesitamos el ID de la competencia actual
        $competenciaIds = [$id_competencia];

        // **CORRECCIÓN CRÍTICA:** Contamos progreso a través de inscripcion.
        $progreso = DB::table('evaluacion')
            ->join('inscripcion', 'evaluacion.id_inscripcion', '=', 'inscripcion.id_inscripcion')
            ->whereIn('evaluacion.id_competencia', $competenciaIds)
            ->distinct()
            ->count('inscripcion.id_competidor');

        return [
            'cantidad_evaluadores' => $cantidad_evaluadores,
            'cantidad_competidores' => $cantidad_competidores,
            'progreso' => $progreso,
        ];
    }

    public function getSubFasesDetails(int $id_area, int $id_nivel, int $id_olimpiada)
    {
        // 1. Obtener AreaNivel (Búsqueda corregida)
        $areaNivel = AreaNivel::whereHas('areaOlimpiada', function(Builder $q) use ($id_area, $id_olimpiada) {
                $q->where('id_area', $id_area)
                  ->where('id_olimpiada', $id_olimpiada);
            })
            ->where('id_nivel', $id_nivel)
            ->first();

        if (!$areaNivel) {
            return collect();
        }

        $id_area_nivel = $areaNivel->id_area_nivel;

        // "Fases" ahora son las Competencias asociadas al AreaNivel
        $competencias = Competencia::where('id_area_nivel', $id_area_nivel)
            ->orderBy('id_fase_global') // Agrupar por fase global
            ->get();

        // Si no hay Competencias (exámenes)
        if ($competencias->isEmpty()) {
            return collect();
        }

        // **CORRECCIÓN CRÍTICA:** Contamos a través de Inscripcion
        $cant_evaluadores = DB::table('evaluador_an')->where('id_area_nivel', $id_area_nivel)->count();
        $cant_estudiantes = DB::table('inscripcion')->where('id_area_nivel', $id_area_nivel)->distinct()->count('id_competidor');

        $competenciaIds = $competencias->pluck('id_competencia');

        // Contar el progreso (Evaluacion -> Inscripcion -> Competidor)
        $evaluatedCounts = DB::table('evaluacion')
            ->join('inscripcion', 'evaluacion.id_inscripcion', '=', 'inscripcion.id_inscripcion')
            ->whereIn('evaluacion.id_competencia', $competenciaIds)
            ->select('evaluacion.id_competencia', DB::raw('COUNT(DISTINCT inscripcion.id_competidor) as count'))
            ->groupBy('evaluacion.id_competencia')
            ->get()
            ->keyBy('id_competencia');

        // Agrupar las "subfases" (Competencias) por su Fase Global (nombre)
        $competenciasGroupedByFase = $competencias->groupBy('id_fase_global');

        return $competenciasGroupedByFase->map(function ($competencias, $idFaseGlobal) use ($cant_estudiantes, $cant_evaluadores, $evaluatedCounts) {
            $faseGlobal = FaseGlobal::find($idFaseGlobal);

            // Mapeo de estados basado en la lógica del código original
            $isFinalizado = $competencias->every(fn($c) => !$c->estado_comp); // Si estado_comp es false (inactivo/finalizado)
            $isEnCurso = $competencias->contains(fn($c) => $c->estado_comp); // Si estado_comp es true (activo)

            $estado = 'NO_INICIADA';
            if ($isEnCurso) {
                $estado = 'EN_EVALUACION';
            } elseif ($isFinalizado) {
                $estado = 'FINALIZADA';
            }

            // Sumar el progreso total de las competencias de esta fase global
            $competidoresEvaluados = $competencias->sum(fn($c) => $evaluatedCounts->get($c->id_competencia)->count ?? 0);

            $progreso = ($cant_estudiantes > 0) ? ($competidoresEvaluados / $cant_estudiantes) * 100 : 0;

            return [
                // Adaptamos a las claves de salida esperadas
                'id_subfase' => $faseGlobal->id_fase_global, // Usamos el ID de FaseGlobal para identificar la "subfase"
                'nombre' => $faseGlobal->nombre_fas_glo, // Columna corregida
                'orden' => $faseGlobal->orden_fas_glo, // Columna corregida
                'estado' => $estado,
                'cant_estudiantes' => $cant_estudiantes,
                'cant_evaluadores' => $cant_evaluadores,
                'progreso' => round($progreso),
            ];
        })->values();
    }
}
