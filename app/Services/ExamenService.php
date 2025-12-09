<?php

namespace App\Services;

use App\Repositories\ExamenRepository;
use App\Model\ExamenConf;
use App\Model\Competencia;

class ExamenService
{
    protected $examenRepository;

    public function __construct(ExamenRepository $examenRepository)
    {
        $this->examenRepository = $examenRepository;
    }

    public function crearExamen(array $data, int $id_competencia): ExamenConf
    {
        $competencia = Competencia::with('examenes')->findOrFail($id_competencia);
        $ponderacionActual = $competencia->examenes->sum('ponderacion');
        $nuevaPonderacion = $data['ponderacion'];

        if (($ponderacionActual + $nuevaPonderacion) > 100) {
            throw new \Exception("La suma de ponderaciones para esta competencia no puede exceder 100. Ponderación actual: {$ponderacionActual}%.");
        }

        return $this->examenRepository->crear($data, $id_competencia);
    }

    public function obtenerExamenesPorCompetencia(int $id_competencia)
    {
        return $this->examenRepository->obtenerPorCompetencia($id_competencia);
    }

    public function obtenerExamenPorId(int $id_examen_conf): ?ExamenConf
    {
        return $this->examenRepository->obtenerPorId($id_examen_conf);
    }

    public function obtenerExamenesPorAreaYNivel(int $id_area, int $id_nivel)
    {
        return $this->examenRepository->obtenerPorAreaYNivel($id_area, $id_nivel);
    }
}