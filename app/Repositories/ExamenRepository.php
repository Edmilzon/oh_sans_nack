<?php

namespace App\Repositories;

use App\Model\ExamenConf;

class ExamenRepository
{

    public function crear(array $data, int $id_competencia): ExamenConf
    {
        $data['id_competencia'] = $id_competencia;
        return ExamenConf::create($data);
    }

    public function obtenerPorCompetencia(int $id_competencia)
    {
        return ExamenConf::where('id_competencia', $id_competencia)->get();
    }

    public function obtenerPorId(int $id_examen_conf): ?ExamenConf
    {
        return ExamenConf::find($id_examen_conf);
    }

    public function obtenerPorAreaYNivel(int $id_area, int $id_nivel)
    {
        return ExamenConf::whereHas('competencia.areaNivel.areaOlimpiada', function ($query) use ($id_area) {
                $query->where('id_area', $id_area);
            })
            ->whereHas('competencia.areaNivel', function ($query) use ($id_nivel) {
                $query->where('id_nivel', $id_nivel);
            })
            ->get();
    }
}