<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use App\Model\Evaluacion;
use Illuminate\Database\Eloquent\Model;

class EvaluacionRepository
{
    /**
     * Crea o actualiza una evaluación.
     *
     * @param array $data Los datos para la evaluación.
     * @param int|null $id_evaluacion El ID de la evaluación a actualizar, o null para crear.
     * @return Model
     */
    public function crearOActualizar(array $data, ?int $id_evaluacion = null): Model
    {
        return Evaluacion::updateOrCreate(
            ['id_evaluacion' => $id_evaluacion],
            $data
        );
    }

    /**
     * Obtiene todas las evaluaciones con estado 'Calificado' para una competencia.
     *
     * @param int $id_competencia
     * @return Collection
     */
    public function getCalificadosPorCompetencia(int $id_competencia): Collection
    {
        return Evaluacion::where('id_competencia', $id_competencia)
            ->where('estado', 'Calificado')
            ->with(['competidor.persona', 'competidor.institucion'])
            ->get();
    }

    /**
     * Obtiene la última evaluación para un competidor específico.
     *
     * @param int $id_competidor
     * @return Evaluacion|null
     */
    public function getPorCompetidor(int $id_competidor): ?Evaluacion
    {
        return Evaluacion::where('id_competidor', $id_competidor)
            ->latest('fecha_evaluacion')
            ->first();
    }
}
