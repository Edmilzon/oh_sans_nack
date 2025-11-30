<?php

namespace App\Services;

use App\Repositories\FaseRepository;
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

    public function obtenerFasesPorAreaNivel(int $id_area_nivel): Collection
    {
        return $this->faseRepository->obtenerPorAreaNivel($id_area_nivel);
    }

    public function crearFaseConCompetencia(array $data)
    {
        return $this->faseRepository->crearConCompetencia($data);
    }

    public function obtenerFasePorId(int $id_fase)
    {
        return $this->faseRepository->obtenerPorId($id_fase);
    }

    public function actualizarFase(int $id_fase, array $data)
    {
        return $this->faseRepository->actualizar($id_fase, $data);
    }

    public function eliminarFase(int $id_fase)
    {
        return $this->faseRepository->eliminar($id_fase);
    }

    public function listarAccionesSistema()
    {
        return $this->faseRepository->listarAccionesSistema();
    }

    public function getConfiguracionAccionesPorGestion(int $idGestion)
    {
        return $this->faseRepository->getConfiguracionAccionesPorGestion($idGestion);
    }

    public function guardarConfiguracionAccionesPorGestion(int $idGestion, array $accionesPorFase)
    {
        return $this->faseRepository->guardarConfiguracionAccionesPorGestion($idGestion, $accionesPorFase);
    }

    public function actualizarAccionHabilitada(int $idGestion, int $idFase, int $idAccion, bool $habilitada)
    {
        return $this->faseRepository->actualizarAccionHabilitada($idGestion, $idFase, $idAccion, $habilitada);
    }
    public function getAccionesHabilitadas(int $idGestion, int $idFase)
    {
        return $this->faseRepository->getAccionesHabilitadas($idGestion, $idFase);
    }

    public function getFaseDetails(int $id_fase)
    {
        return $this->faseRepository->getFaseDetails($id_fase);
    }

    public function getSubFasesDetails(int $id_area, int $id_nivel, int $id_olimpiada)
    {
        return $this->faseRepository->getSubFasesDetails($id_area, $id_nivel, $id_olimpiada);
    }
}
