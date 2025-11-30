<?php

namespace App\Services;

use App\Repositories\EvaluacionRepository;
use App\Model\Competidor;
use App\Model\Evaluacion;
use App\Model\Competencia;
use App\Model\Inscripcion;
use App\Events\CompetidorBloqueado;
use App\Events\CompetidorLiberado;
use Illuminate\Support\Facades\DB;
use Exception;

class EvaluacionService
{
    protected $evaluacionRepository;

    public function __construct(EvaluacionRepository $evaluacionRepository)
    {
        $this->evaluacionRepository = $evaluacionRepository;
    }

    /**
     * Crea una evaluación y la marca como "EN PROCESO", además de emitir un evento de bloqueo.
     *
     * @param array $data Los datos para la evaluación (id_competidor, id_evaluadorAN).
     * @param int $id_competencia El ID de la competencia asociada.
     * @return \App\Model\Evaluacion
     * @throws \Exception
     */
    public function crearEvaluacion(array $data, int $id_competencia): \App\Model\Evaluacion
    {
        return DB::transaction(function () use ($data, $id_competencia) {
            $competencia = Competencia::findOrFail($id_competencia);

            // 1. **CRÍTICO:** Obtener la inscripción para esta competencia y competidor
            $inscripcion = Inscripcion::where('id_competidor', $data['id_competidor'])
                                      ->where('id_area_nivel', $competencia->id_area_nivel)
                                      ->firstOrFail();

            // 2. Buscar evaluación existente
            $evaluacionExistente = $this->evaluacionRepository->buscarPorInscripcionYCompetencia(
                $inscripcion->id_inscripcion,
                $id_competencia
            );

            // Verificar si está EN PROCESO
            if ($evaluacionExistente && $evaluacionExistente->estado_competidor_eva === 'EN PROCESO') {
                throw new Exception("Este competidor ya está siendo evaluado por otra persona.");
            }

            // 3. Mapeo de datos para el Repositorio (BD V8)
            $datosEvaluacion = [
                'id_inscripcion'        => $inscripcion->id_inscripcion,
                'id_competencia'        => $id_competencia,
                'id_evaluador_an'       => $data['id_evaluadorAN'],
                'nota_evalu'            => 0,
                'estado_competidor_eva' => 'EN PROCESO',
                'fecha_evalu'           => now(), // Columna corregida
            ];

            // 4. Crear o actualizar (usando el ID existente si lo hay)
            $evaluacion = $this->evaluacionRepository->crearOActualizar(
                $datosEvaluacion,
                optional($evaluacionExistente)->id_evaluacion
            );

            // 5. Emitir evento de bloqueo
            broadcast(new CompetidorBloqueado($inscripcion->id_competidor, $evaluacion->id_evaluador_an, $id_competencia))->toOthers();

            return $evaluacion;
        });
    }

    /**
     * Actualiza una evaluación existente, manejando el mapeo de campos.
     *
     * @param int $id_evaluacion El ID de la evaluación a actualizar.
     * @param array $data Los datos para actualizar (nota, observaciones, estado).
     * @return \App\Model\Evaluacion
     */
    public function actualizarEvaluacion(int $id_evaluacion, array $data)
    {
        return DB::transaction(function () use ($id_evaluacion, $data) {

            // Mapeo de campos de entrada (Front) a columnas de BD (V8)
            $datosParaRepo = [];
            if (isset($data['nota'])) $datosParaRepo['nota_evalu'] = $data['nota'];
            if (isset($data['observaciones'])) $datosParaRepo['observacion_evalu'] = $data['observaciones'];
            if (isset($data['estado'])) $datosParaRepo['estado_competidor_eva'] = $data['estado']; // Columna corregida

            // Actualizar fecha de evaluación si hay cambios
            if (!empty($datosParaRepo)) {
                $datosParaRepo['fecha_evalu'] = now(); // Columna corregida
            }

            $evaluacion = $this->evaluacionRepository->crearOActualizar($datosParaRepo, $id_evaluacion);

            // Lógica de actualización de estado de la Competencia
            if (isset($data['estado'])) {
                $competencia = Competencia::findOrFail($evaluacion->id_competencia);
                $totalEvaluaciones = Evaluacion::where('id_competencia', $evaluacion->id_competencia)->count();

                // Usamos la columna corregida para el estado
                $evaluacionesCalificadas = Evaluacion::where('id_competencia', $evaluacion->id_competencia)
                    ->where('estado_competidor_eva', 'CALIFICADO')
                    ->count();

                // Columna corregida: estado_comp (boolean)
                if ($totalEvaluaciones > 0 && $totalEvaluaciones === $evaluacionesCalificadas) {
                    $competencia->estado_comp = false; // Asumimos false = Finalizado
                } else {
                    $competencia->estado_comp = true; // Asumimos true = En Proceso
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
     * @param array $data (nota, observaciones)
     * @return \App\Model\Evaluacion
     */
    public function finalizarCalificacion(int $id_evaluacion, array $data): \App\Model\Evaluacion
    {
        $evaluacion = Evaluacion::findOrFail($id_evaluacion);

        if ($evaluacion->estado_competidor_eva !== 'EN PROCESO') { // Columna corregida
            throw new Exception("Solo se puede calificar una evaluación que está 'EN PROCESO'.");
        }

        $datosFinales = [
            'nota' => $data['nota'],
            'observaciones' => $data['observaciones'] ?? null,
            'estado' => 'CALIFICADO', // Mapeado en actualizarEvaluacion
        ];

        $evaluacionActualizada = $this->actualizarEvaluacion($id_evaluacion, $datosFinales);

        // El id_competidor se obtiene a través de la relación
        $id_competidor = $evaluacionActualizada->inscripcion->id_competidor;

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
        // Se corrigió el nombre del método a 'getUltimaPorCompetidor'
        return $this->evaluacionRepository->getUltimaPorCompetidor($id_competidor);
    }
}
