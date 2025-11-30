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
        // Si viene el ID de evaluación, lo usamos para buscar y actualizar.
        if ($id_evaluacion) {
            $attributes = ['id_evaluacion' => $id_evaluacion];
        } else {
            // Si es nueva, usamos las claves de unicidad/relación para crear o encontrar (Inscripción + Competencia).
            $attributes = [
                'id_inscripcion' => $data['id_inscripcion'],
                'id_competencia' => $data['id_competencia']
            ];
        }

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
        // Se resuelven los conflictos: se usa 'Calificado' o 'CALIFICADO' dependiendo del Seeder/BD.
        // Asumo que se debe usar 'Calificado' (o 'En Proceso', 'Finalizado') según la convención de strings.
        return Evaluacion::where('id_competencia', $id_competencia)
            ->where('estado_competidor_eva', 'Calificado') // Columna y valor corregidos
            // Relación profunda: Evaluacion -> Inscripcion -> Competidor -> Persona/Institucion
            ->with(['inscripcion.competidor.persona', 'inscripcion.competidor.institucion'])
            ->get();
    }

    /**
     * Obtiene la última evaluación para un competidor específico.
     * El nombre fue renombrado a 'getUltimaPorCompetidor'.
     *
     * @param int $id_competidor
     * @return Evaluacion|null
     */
    public function getUltimaPorCompetidor(int $id_competidor): ?Evaluacion
    {
        // Usamos whereHas (relación Eloquent) que es más limpio y eficiente que el join crudo.
        return Evaluacion::whereHas('inscripcion', function ($query) use ($id_competidor) {
                $query->where('id_competidor', $id_competidor);
            })
            // Columna corregida: fecha_evalu
            ->latest('fecha_evalu')
            ->first();
    }

    /**
     * Busca una evaluación específica por inscripción y competencia.
     * Este método fue añadido en la versión del merge.
     */
    public function buscarPorInscripcionYCompetencia(int $id_inscripcion, int $id_competencia): ?Evaluacion
    {
        return Evaluacion::where('id_inscripcion', $id_inscripcion)
            ->where('id_competencia', $id_competencia)
            ->first();
    }
}
