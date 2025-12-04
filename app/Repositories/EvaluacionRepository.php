<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use App\Model\Evaluacion;
use Illuminate\Database\Eloquent\Model;

class EvaluacionRepository
{

    public function crearOActualizar(array $data, ?int $id_evaluacion = null): Model
    {
        $attributes = $id_evaluacion 
            ? ['id_evaluacion' => $id_evaluacion] 
            : ['id_competidor' => $data['id_competidor'], 'id_examen_conf' => $data['id_examen_conf']];
            
        return Evaluacion::updateOrCreate($attributes, $data);
    }

    public function getCalificadosPorCompetencia(int $id_competencia): Collection
    {
        return Evaluacion::whereHas('examen.competencia', function ($query) use ($id_competencia) {
                $query->where('id_competencia', $id_competencia);
            })
            ->where('estado_competidor', 'CALIFICADO')
            ->with(['competidor.persona', 'competidor.institucion', 'examen'])
            ->get();
    }

    public function getUltimaPorCompetidor(int $id_competidor): ?Evaluacion
    {
        return Evaluacion::where('id_competidor', $id_competidor)
            ->latest('fecha')
            ->first();
    }

    public function buscarPorCompetidorYExamen(int $id_competidor, int $id_examen_conf): ?Evaluacion
    {
        return Evaluacion::where('id_competidor', $id_competidor)
            ->where('id_examen_conf', $id_examen_conf)
            ->first();
    }
}
