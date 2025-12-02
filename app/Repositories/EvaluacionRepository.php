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
        $attributes = $id_evaluacion 
            ? ['id_evaluacion' => $id_evaluacion] 
            : ['id_competidor' => $data['id_competidor'], 'id_competencia' => $data['id_competencia']];
            
        return Evaluacion::updateOrCreate($attributes, $data);
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
            ->where('estado_competidor', 'CALIFICADO')
            ->with(['competidor.persona', 'competidor.institucion'])
            ->get();
    }

    /**
     * Obtiene la última evaluación para un competidor específico.
     *
     * @param int $id_competidor
     * @return Evaluacion|null
     */
    public function getUltimaPorCompetidor(int $id_competidor): ?Evaluacion
    {
        return Evaluacion::where('id_competidor', $id_competidor)
            ->latest('fecha')
            ->first();
    }

    public function buscarPorCompetidorYCompetencia(int $id_competidor, int $id_competencia): ?Evaluacion
    {
        return Evaluacion::where('id_competidor', $id_competidor)
            ->where('id_competencia', $id_competencia)
            ->first();
    }
}
