<?php

namespace App\Services;

use App\Repositories\EvaluacionRepository;
use App\Model\Competidor;
use App\Model\ExamenConf;
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

    public function crearEvaluacion(array $data, int $id_examen_conf): \App\Model\Evaluacion
    {
        return DB::transaction(function () use ($data, $id_examen_conf) {

            $id_competidor = $data['id_competidor'];
            Competidor::findOrFail($id_competidor);
            $examen = ExamenConf::findOrFail($id_examen_conf);

            $evaluacionExistente = $this->evaluacionRepository->buscarPorCompetidorYExamen($id_competidor, $id_examen_conf);

            if ($evaluacionExistente && $evaluacionExistente->estado_competidor === 'EN PROCESO') {
                throw new \Exception("Este competidor ya está siendo evaluado por otra persona.");
            }

            $datosEvaluacion = [
                'id_competidor' => $id_competidor,
                'id_examen_conf' => $id_examen_conf,
                'id_evaluador_an' => $data['id_evaluador_an'],
                'nota' => 0,
                'estado_competidor' => 'EN PROCESO',
                'fecha' => now(),
                'estado' => false,
            ];

            $evaluacion = $this->evaluacionRepository->crearOActualizar($datosEvaluacion, optional($evaluacionExistente)->id_evaluacion);
            
            broadcast(new CompetidorBloqueado($id_competidor, $evaluacion->id_evaluador_an, $examen->id_competencia))->toOthers();

            return $evaluacion;
        });
    }

    public function actualizarEvaluacion(int $id_evaluacion, array $data)
    {
        return DB::transaction(function () use ($id_evaluacion, $data) {
            $evaluacion = $this->evaluacionRepository->crearOActualizar($data, $id_evaluacion);

            if (isset($data['estado_competidor'])) {
                $competencia = $evaluacion->examen->competencia;
                $ids_examenes = $competencia->examenes()->pluck('id_examen_conf');

                $totalEvaluaciones = Evaluacion::whereIn('id_examen_conf', $ids_examenes)->count();
                $evaluacionesCalificadas = Evaluacion::whereIn('id_examen_conf', $ids_examenes)
                    ->where('estado_competidor', 'CALIFICADO')
                    ->count();

                if ($totalEvaluaciones > 0 && $totalEvaluaciones === $evaluacionesCalificadas) {
                    $competencia->estado = true;
                } else {
                    $competencia->estado = false;
                }
                $competencia->save();
            }

            return $evaluacion;
        });
    }

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
            'estado' => true,
        ];
        
        $evaluacionActualizada = $this->actualizarEvaluacion($id_evaluacion, $datosFinales);

        $id_competidor = $evaluacionActualizada->id_competidor;
        
        broadcast(new CompetidorLiberado($id_competidor, $evaluacionActualizada->examen->id_competencia))->toOthers();

        return $evaluacionActualizada;
    }

    public function descalificarCompetidor(int $id_evaluacion, array $data): \App\Model\Evaluacion
    {
        $evaluacion = Evaluacion::findOrFail($id_evaluacion);
        if ($evaluacion->estado_competidor !== 'EN PROCESO') {
            throw new \Exception("Solo se puede descalificar una evaluación que está 'EN PROCESO'.");
        }

        $datosDescalificacion = [
            'nota' => 0,
            'observacion' => $data['observaciones'],
            'estado_competidor' => 'DESCALIFICADO',
            'fecha' => now(),
            'estado' => true,
        ];

        $evaluacionActualizada = $this->actualizarEvaluacion($id_evaluacion, $datosDescalificacion);

        broadcast(new CompetidorLiberado($evaluacionActualizada->id_competidor, $evaluacionActualizada->examen->id_competencia))->toOthers();

        return $evaluacionActualizada;
    }

    public function getCalificadosPorCompetencia(int $id_competencia)
    {
        return $this->evaluacionRepository->getCalificadosPorCompetencia($id_competencia);
    }

    public function getUltimaPorCompetidor(int $id_competidor)
    {
        return $this->evaluacionRepository->getUltimaPorCompetidor($id_competidor);
    }
}