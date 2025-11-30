<?php

namespace App\Services;

use App\Repositories\FaseRepository;
use App\Model\Competencia; // Reemplaza App\Model\Fase
use Illuminate\Database\Eloquent\Collection;

class FaseService
{
    protected $faseRepository;

    public function __construct(FaseRepository $faseRepository)
    {
        $this->faseRepository = $faseRepository;
    }

    public function obtenerFasesGlobales(): Collection
    {
        return $this->faseRepository->obtenerFasesGlobales();
    }

    /**
     * Obtiene las Competencias (antiguas fases) asociadas a un AreaNivel.
     * @param int $id_area_nivel
     * @return Collection
     */
    public function obtenerFasesPorAreaNivel(int $id_area_nivel): Collection
    {
        return $this->faseRepository->obtenerPorAreaNivel($id_area_nivel);
    }

    /**
     * Crea una Competencia (antigua fase).
     * @param array $data
     * @return Competencia
     */
    public function crearFaseConCompetencia(array $data): Competencia
    {
        // El Repositorio crea directamente la Competencia
        return $this->faseRepository->crearConCompetencia($data);
    }

    /**
     * Obtiene una Competencia (antigua fase) por su ID.
     * @param int $id_competencia
     * @return Competencia|null
     */
    public function obtenerFasePorId(int $id_competencia): ?Competencia
    {
        return $this->faseRepository->obtenerPorId($id_competencia);
    }

    /**
     * Actualiza una Competencia.
     * @param int $id_competencia
     * @param array $data
     * @return bool
     */
    public function actualizarFase(int $id_competencia, array $data): bool
    {
        return $this->faseRepository->actualizar($id_competencia, $data);
    }

    /**
     * Elimina una Competencia.
     * @param int $id_competencia
     * @return bool
     */
    public function eliminarFase(int $id_competencia): bool
    {
        return $this->faseRepository->eliminar($id_competencia);
    }

    public function listarAccionesSistema(): Collection
    {
        return $this->faseRepository->listarAccionesSistema();
    }

    public function getConfiguracionAccionesPorGestion(int $idGestion): array
    {
        return $this->faseRepository->getConfiguracionAccionesPorGestion($idGestion);
    }

    public function guardarConfiguracionAccionesPorGestion(int $idGestion, array $accionesPorFase): void
    {
        $this->faseRepository->guardarConfiguracionAccionesPorGestion($idGestion, $accionesPorFase);
    }

    /**
     * @param int $idFase Ahora es id_fase_global
     */
    public function actualizarAccionHabilitada(int $idGestion, int $idFase, int $idAccion, bool $habilitada): void
    {
        $this->faseRepository->actualizarAccionHabilitada($idGestion, $idFase, $idAccion, $habilitada);
    }

    /**
     * @param int $idFase Ahora es id_fase_global
     */
    public function getAccionesHabilitadas(int $idGestion, int $idFase): Collection
    {
        return $this->faseRepository->getAccionesHabilitadas($idGestion, $idFase);
    }

    /**
     * Obtiene detalles de una Competencia (antigua fase).
     * @param int $id_competencia
     * @return array
     */
    public function getFaseDetails(int $id_competencia): ?array
    {
        return $this->faseRepository->getFaseDetails($id_competencia);
    }

    public function getSubFasesDetails(int $id_area, int $id_nivel, int $id_olimpiada): Collection
    {
        return $this->faseRepository->getSubFasesDetails($id_area, $id_nivel, $id_olimpiada);
    }
}
