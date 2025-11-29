<?php

namespace App\Services;

use App\Repositories\EvaluacionRepository;
use App\Model\Competidor;
use App\Model\Evaluacion;
use App\Model\Parametro;
use App\Model\Competencia;
use Illuminate\Support\Facades\DB;

class EvaluacionService
{
    protected $evaluacionRepository;

    public function __construct(EvaluacionRepository $evaluacionRepository)
    {
        $this->evaluacionRepository = $evaluacionRepository;
    }

    /**
     * Crea o actualiza una evaluación y actualiza el estado de la competencia.
     *
     * @param array $data Los datos para la evaluación.
     * @param int $id_competencia El ID de la competencia asociada.
     * @return \App\Model\Evaluacion
     */
    public function crearEvaluacion(array $data, int $id_competencia): \App\Model\Evaluacion
    {
        return DB::transaction(function () use ($data, $id_competencia) {
            $competidor = Competidor::findOrFail($data['id_competidor']);
            $parametro = Parametro::where('id_area_nivel', $competidor->id_area_nivel)->first();
    
            if (!$parametro) {
                throw new \Exception("No se encontraron parámetros de calificación para el área y nivel del competidor.");
            }
    
            $data['id_competencia'] = $id_competencia;
            $data['id_parametro'] = $parametro->id_parametro;
            $data['estado'] = 'En Proceso'; 
            $data['nota'] = 0; 
    
            $evaluacion = $this->evaluacionRepository->crearOActualizar($data);
    
            return $evaluacion;
        });
    }

    /**
     * Actualiza una evaluación existente.
     *
     * @param int $id_evaluacion El ID de la evaluación a actualizar.
     * @param array $data Los datos para actualizar.
     * @return \App\Model\Evaluacion
     */
    public function actualizarEvaluacion(int $id_evaluacion, array $data)
    {
        return DB::transaction(function () use ($id_evaluacion, $data) {
            $evaluacion = $this->evaluacionRepository->crearOActualizar($data, $id_evaluacion);

            if (isset($data['estado'])) {
                $competencia = Competencia::findOrFail($evaluacion->id_competencia);
                $totalEvaluaciones = Evaluacion::where('id_competencia', $evaluacion->id_competencia)->count();
                $evaluacionesCalificadas = Evaluacion::where('id_competencia', $evaluacion->id_competencia)->where('estado', 'Calificado')->count();

                if ($totalEvaluaciones > 0 && $totalEvaluaciones === $evaluacionesCalificadas) {
                    $competencia->estado = 'Calificado';
                } else {
                    $competencia->estado = 'En Calificación';
                }
                $competencia->save();
            }

            return $evaluacion;
        });
    }

    /**
     * Finaliza el proceso de calificación, guarda la nota y cambia el estado a 'Calificado'.
     *
     * @param int $id_evaluacion
     * @param array $data
     * @return \App\Model\Evaluacion
     */
    public function finalizarCalificacion(int $id_evaluacion, array $data): \App\Model\Evaluacion
    {
        $evaluacion = Evaluacion::findOrFail($id_evaluacion);
        if ($evaluacion->estado !== 'En Proceso') {
            throw new \Exception("Solo se puede calificar una evaluación que está 'En Proceso'.");
        }

        $data['estado'] = 'Calificado';
        $data['fecha_evaluacion'] = now();
        return $this->actualizarEvaluacion($id_evaluacion, $data);
    }

    // Los otros métodos permanecen igual...
    /**
     * Obtiene todas las evaluaciones calificadas para una competencia.
     *
     * @param int $id_competencia
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCalificadosPorCompetencia(int $id_competencia)
    {
        return $this->evaluacionRepository->getCalificadosPorCompetencia($id_competencia);
    }

    /**
     * Obtiene la última evaluación de un competidor específico.
     *
     * @param int $id_competidor
     * @return \App\Model\Evaluacion|null
     */
    public function getEvaluacionPorCompetidor(int $id_competidor)
    {
        return $this->evaluacionRepository->getPorCompetidor($id_competidor);
    }
}