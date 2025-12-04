<?php

namespace App\Repositories;

use App\Model\FaseGlobal;
use App\Model\Competencia;
use App\Model\AccionSistema;
use App\Model\ConfiguracionAccion;
use App\Model\Olimpiada;
use App\Model\AreaNivel;
use App\Model\ResponsableArea;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FaseRepository
{

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

        $responsableArea = ResponsableArea::where('id_area_olimpiada', $areaNivel->id_area_olimpiada)->first();
        if ($responsableArea && $responsableArea->usuario) {
            $idResponsable = $responsableArea->usuario->id_persona;
        }

        $competencia = Competencia::create([
            'id_area_nivel'  => $idAreaNivel,
            'id_persona'     => $idResponsable,
            'nombre_examen'  => $data['nombre'],
            'fecha_inicio'   => $data['fecha_inicio'],
            'fecha_fin'      => $data['fecha_fin'],
            'ponderacion'    => $data['ponderacion'] ?? 0,
            'estado'         => false,
            'es_avalado'     => false,
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

    public function actualizarEstadoCompetencia(int $idCompetencia, bool $estadoBooleano): array
    {
        $competencia = Competencia::findOrFail($idCompetencia);

        $competencia->update([
            'estado' => $estadoBooleano
        ]);

        $estadoTexto = $estadoBooleano ? 'EN_EVALUACION' : 'FINALIZADA';

        return [
            'id_subfase' => $competencia->id_competencia,
            'estado'     => $estadoTexto
        ];
    }

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
        if ($fasesGlobales->isEmpty()) {
             $fasesGlobales = FaseGlobal::orderBy('orden')->get();
        }

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

    public function getAccionesHabilitadas(int $idFaseGlobal): Collection
    {
        return AccionSistema::whereHas('configuraciones', function($q) use ($idFaseGlobal) {
            $q->where('id_fase_global', $idFaseGlobal)
              ->where('habilitada', true);
        })->get();
    }

    public function getSubFasesDetails(int $idArea, int $idNivel, int $idOlimpiada): \Illuminate\Support\Collection
    {

        $areaNivel = \App\Model\AreaNivel::where('id_nivel', $idNivel)
            ->whereHas('areaOlimpiada', function($q) use ($idArea, $idOlimpiada) {
                $q->where('id_area', $idArea)
                ->where('id_olimpiada', $idOlimpiada);
            })->first();

        if (!$areaNivel) return collect([]);

        $competencias = \App\Model\Competencia::where('id_area_nivel', $areaNivel->id_area_nivel)
            ->orderBy('fecha_inicio')
            ->get();

        return $competencias->map(function($comp) use ($areaNivel) {

            $cantEstudiantes = \App\Model\Competidor::where('id_area_nivel', $areaNivel->id_area_nivel)->count();

            $cantEvaluadores = \App\Model\EvaluadorAn::where('id_area_nivel', $areaNivel->id_area_nivel)
                ->where('estado', true)
                ->count();

            $evaluados = \App\Model\Evaluacion::where('id_competencia', $comp->id_competencia)
                ->distinct('id_competidor')
                ->count();

            $progreso = ($cantEstudiantes > 0) ? round(($evaluados / $cantEstudiantes) * 100) : 0;

            $hoy = now();
            $estadoStr = "NO_INICIADA";

            if ($comp->estado) {

                if ($hoy->gt($comp->fecha_fin)) {
                    $estadoStr = "FINALIZADA";
                } else {
                    $estadoStr = "EN_EVALUACION";
                }
            } else {

                if ($hoy->gt($comp->fecha_fin)) {
                    $estadoStr = "FINALIZADA";
                }
            }

            return [
                'id_subfase'       => $comp->id_competencia,
                'nombre'           => $comp->nombre_examen,
                'orden'            => 1,
                'estado'           => $estadoStr,
                'cant_estudiantes' => $cantEstudiantes,
                'cant_evaluadores' => $cantEvaluadores,
                'progreso'         => $progreso
            ];
        });
    }

    public function getByOlimpiadaId(int $idOlimpiada): Collection
    {
        return FaseGlobal::where('id_olimpiada', $idOlimpiada)
            ->orderBy('orden', 'asc')
            ->get();
    }
}
