<?php

namespace App\Services;

use App\Repositories\EvaluacionRepository;
use App\Model\Competidor;
use App\Model\Evaluacion;
use App\Model\Competencia;
use App\Events\CompetidorBloqueado;
use App\Events\CompetidorLiberado;
use Illuminate\Support\Facades\DB;

class EvaluacionService
{
    protected $evaluacionRepository;

    public function __construct(EvaluacionRepository $evaluacionRepository)
    {
        $this->evaluacionRepository = $evaluacionRepository;
    }

    /**
     * Crea una evaluación y la marca como "En Proceso".
     *
     * @param array $data Los datos para la evaluación.
     * @param int $id_competencia El ID de la competencia asociada.
     * @return \App\Model\Evaluacion
     * @throws \Exception
     */
    public function crearEvaluacion(array $data, int $id_competencia): \App\Model\Evaluacion
    {
        return DB::transaction(function () use ($data, $id_competencia) {
            // Se busca el competidor directamente.
            $id_competidor = $data['id_competidor'];
            Competidor::findOrFail($id_competidor); // Asegura que el competidor exista.

            $evaluacionExistente = $this->evaluacionRepository->buscarPorCompetidorYCompetencia($id_competidor, $id_competencia);

            if ($evaluacionExistente && $evaluacionExistente->estado_competidor === 'EN PROCESO') {
                throw new \Exception("Este competidor ya está siendo evaluado por otra persona.");
            }

            $datosEvaluacion = [
                'id_competidor' => $id_competidor,
                'id_competencia' => $id_competencia,
                'id_evaluador_an' => $data['id_evaluador_an'],
                'nota' => 0,
                'estado_competidor' => 'EN PROCESO',
                'fecha' => now(),
                'estado' => false, // Pendiente de finalizar
            ];

            $evaluacion = $this->evaluacionRepository->crearOActualizar($datosEvaluacion, optional($evaluacionExistente)->id_evaluacion);
            
            broadcast(new CompetidorBloqueado($id_competidor, $evaluacion->id_evaluador_an, $id_competencia))->toOthers();

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

            // Lógica para actualizar estado de la competencia
            if (isset($data['estado_competidor'])) {
                $competencia = Competencia::findOrFail($evaluacion->id_competencia);
                $totalEvaluaciones = Evaluacion::where('id_competencia', $evaluacion->id_competencia)->count();
                $evaluacionesCalificadas = Evaluacion::where('id_competencia', $evaluacion->id_competencia)->where('estado_competidor', 'CALIFICADO')->count();

                // Asumiendo que la competencia tiene un campo 'estado_comp'
                if ($totalEvaluaciones > 0 && $totalEvaluaciones === $evaluacionesCalificadas) {
                    $competencia->estado_comp = 'Calificado'; 
                } else {
                    $competencia->estado_comp = 'En Calificación';
                }
                $competencia->save();
            }

            return $evaluacion;
        });
    }

    /**
     * Finaliza el proceso de calificación, guarda la nota y cambia el estado a 'CALIFICADO'.
     *
     * @param int $id_evaluacion
     * @param array $data
     * @return \App\Model\Evaluacion
     */
    public function finalizarCalificacion(int $id_evaluacion, array $data): \App\Model\Evaluacion
    {
        $evaluacion = Evaluacion::findOrFail($id_evaluacion);
        if ($evaluacion->estado_competidor !== 'EN PROCESO') {
            throw new \Exception("Solo se puede calificar una evaluación que está 'EN PROCESO'.");
        }

        $datosFinales = [
            'nota' => $data['nota'],
            'observacion' => $data['observaciones'] ?? null,
            'estado_competidor' => 'CALIFICADO',
            'fecha' => now(),
            'estado' => true, // Finalizado
        ];
        
        $evaluacionActualizada = $this->actualizarEvaluacion($id_evaluacion, $datosFinales);

        $id_competidor = $evaluacionActualizada->id_competidor;
        
        broadcast(new CompetidorLiberado($id_competidor, $evaluacionActualizada->id_competencia))->toOthers();

        return $evaluacionActualizada;
    }

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
    public function getUltimaPorCompetidor(int $id_competidor)
    {
        return $this->evaluacionRepository->getUltimaPorCompetidor($id_competidor);
    }
}