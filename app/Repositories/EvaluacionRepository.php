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
        $attributes = $id_evaluacion ? ['id_evaluacion' => $id_evaluacion] : ['id_inscripcion' => $data['id_inscripcion'], 'id_competencia' => $data['id_competencia']];
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
            ->where('estado_competidor_eva', 'CALIFICADO')
            ->with(['inscripcion.competidor.persona', 'inscripcion.competidor.institucion'])
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
        return Evaluacion::join('inscripcion', 'evaluacion.id_inscripcion', '=', 'inscripcion.id_inscripcion')
            ->where('inscripcion.id_competidor', $id_competidor)
            ->latest('evaluacion.fecha_evalu')
            ->first();
    }

    public function buscarPorInscripcionYCompetencia(int $id_inscripcion, int $id_competencia): ?Evaluacion
    {
        return Evaluacion::where('id_inscripcion', $id_inscripcion)
            ->where('id_competencia', $id_competencia)
            ->first();
    }
}
