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
}