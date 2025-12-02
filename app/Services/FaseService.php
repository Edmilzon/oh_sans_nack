<?php

namespace App\Services;

use App\Repositories\FaseRepository;
// CRÍTICO: Usamos Support\Collection para compatibilidad de tipos
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FaseService
{
    public function __construct(
        protected FaseRepository $repo
    ) {}

    // ==========================================
    // GESTIÓN GLOBAL
    // ==========================================

    public function obtenerFasesGlobales(): Collection
    {
        return $this->repo->obtenerFasesGlobales();
    }

    public function listarAccionesSistema(): Collection
    {
        return $this->repo->listarAccionesSistema();
    }

    public function getConfiguracionAccionesPorGestion(int $idGestion): array
    {
        return $this->repo->getConfiguracionMatriz($idGestion);
    }

    public function guardarConfiguracionAcciones(int $idGestion, array $accionesPorFase): void
    {
        DB::transaction(function () use ($accionesPorFase) {
            $this->repo->guardarConfiguracion($accionesPorFase);
        });
    }

    // ALIAS: Método requerido por el Controlador
    public function guardarConfiguracionAccionesPorGestion(int $idGestion, array $accionesPorFase): void
    {
        $this->guardarConfiguracionAcciones($idGestion, $accionesPorFase);
    }

    public function actualizarAccionHabilitada(int $idGestion, int $idFase, int $idAccion, bool $habilitada): void
    {
        $this->repo->actualizarAccionUnica($idFase, $idAccion, $habilitada);
    }

    // MÉTODO FALTANTE: Agregado para solucionar error P1013
    public function getAccionesHabilitadas(int $idGestion, int $idFase): Collection
    {
        return $this->repo->getAccionesHabilitadas($idFase);
    }

    // ==========================================
    // GESTIÓN ESPECÍFICA (Competencias)
    // ==========================================

    public function obtenerFasesPorAreaNivel(int $idAreaNivel): Collection
    {
        return $this->repo->obtenerPorAreaNivel($idAreaNivel);
    }

    public function crearFase(array $data, int $idAreaNivel)
    {
        return DB::transaction(function () use ($data, $idAreaNivel) {
            return $this->repo->crearCompetencia($data, $idAreaNivel);
        });
    }

    // ALIAS: Método requerido por el Controlador
    public function obtenerFasePorId(int $id)
    {
        return $this->repo->findCompetenciaById($id);
    }

    // ALIAS: Método requerido por el Controlador
    public function getFaseDetails(int $id)
    {
        return $this->repo->findCompetenciaById($id);
    }

    public function actualizarFase(int $idFase, array $data)
    {
        $competencia = $this->repo->findCompetenciaById($idFase);
        if ($competencia) {
            $this->repo->updateCompetencia($competencia, $data);
            return $competencia->fresh();
        }
        return null;
    }

    public function eliminarFase(int $idFase): bool
    {
        $competencia = $this->repo->findCompetenciaById($idFase);
        if ($competencia) {
            return $this->repo->deleteCompetencia($competencia);
        }
        return false;
    }

    public function cambiarEstadoFase(int $idFase, string $nuevoEstado)
    {
        $estadoBooleano = ($nuevoEstado === 'EN_EVALUACION');
        return $this->repo->actualizarEstadoCompetencia($idFase, $estadoBooleano);
    }

    // ==========================================
    // REPORTES
    // ==========================================

    public function getSubFasesDetails(int $idArea, int $idNivel, int $idOlimpiada): Collection
    {
        return $this->repo->getSubFasesDetails($idArea, $idNivel, $idOlimpiada);
    }
}
