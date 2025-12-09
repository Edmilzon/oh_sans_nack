<?php

namespace App\Services;

use App\Repositories\CompetenciaRepository;
use App\Model\Competencia;

class CompetenciaService
{
    protected $competenciaRepository;

    public function __construct(CompetenciaRepository $competenciaRepository)
    {
        $this->competenciaRepository = $competenciaRepository;
    }

    public function crearCompetencia(array $data): Competencia
    {
        return $this->competenciaRepository->crear($data);
    }

    public function obtenerCompetencias()
    {
        return $this->competenciaRepository->obtenerTodas();
    }

    public function obtenerCompetenciaPorId(int $id_competencia): ?Competencia
    {
        return $this->competenciaRepository->obtenerPorId($id_competencia);
    }

    public function obtenerCompetenciaPorAreaYNivel(int $id_area, int $id_nivel): ?Competencia
    {
        return $this->competenciaRepository->obtenerPorAreaYNivel($id_area, $id_nivel);
    }

    public function obtenerCompetenciasPorAreaNivelId(int $id_area_nivel)
    {
        return $this->competenciaRepository->obtenerPorAreaNivelId($id_area_nivel);
    }
}