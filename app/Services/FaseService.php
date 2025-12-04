<?php

namespace App\Services;

use App\Repositories\FaseRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Repositories\OlimpiadaRepository;

class FaseService
{
    public function __construct(
        protected FaseRepository $repo,
        protected FaseRepository $faseRepository,
        protected OlimpiadaRepository $olimpiadaRepository
    ) {}

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

    public function guardarConfiguracionAccionesPorGestion(int $idGestion, array $accionesPorFase): void
    {
        $this->guardarConfiguracionAcciones($idGestion, $accionesPorFase);
    }

    public function actualizarAccionHabilitada(int $idGestion, int $idFase, int $idAccion, bool $habilitada): void
    {
        $this->repo->actualizarAccionUnica($idFase, $idAccion, $habilitada);
    }

    public function getAccionesHabilitadas(int $idGestion, int $idFase): Collection
    {
        return $this->repo->getAccionesHabilitadas($idFase);
    }

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

    public function obtenerFasePorId(int $id)
    {
        return $this->repo->findCompetenciaById($id);
    }

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

    public function getSubFasesDetails(int $idArea, int $idNivel, int $idOlimpiada): Collection
    {
        return $this->repo->getSubFasesDetails($idArea, $idNivel, $idOlimpiada);
    }

    public function listarFasesDeOlimpiadaActual(): Collection
    {
        $olimpiada = $this->olimpiadaRepository->obtenerMasReciente();

        if (!$olimpiada) {
            return new Collection();
        }

        return $this->faseRepository->getByOlimpiadaId($olimpiada->id_olimpiada);
    }
}
