<?php

namespace App\Repositories;

use App\Model\ExamenConf;

class ExamenRepository
{
    /**
     * Crea un nuevo registro de ExamenConf en la base de datos.
     *
     * @param array $data
     * @param int $id_competencia
     * @return ExamenConf
     */
    public function crear(array $data, int $id_competencia): ExamenConf
    {
        $data['id_competencia'] = $id_competencia;
        return ExamenConf::create($data);
    }

    /**
     * Obtiene todos los exámenes de una competencia.
     *
     * @param int $id_competencia
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function obtenerPorCompetencia(int $id_competencia)
    {
        return ExamenConf::where('id_competencia', $id_competencia)->get();
    }

    /**
     * Obtiene un examen por su ID.
     *
     * @param int $id_examen_conf
     * @return ExamenConf|null
     */
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