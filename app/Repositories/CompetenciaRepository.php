<?php

namespace App\Repositories;

use App\Model\Competencia;

class CompetenciaRepository
{

    public function crear(array $data): Competencia
    {
        return Competencia::create([
            'id_area_nivel' => $data['id_area_nivel'],
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin' => $data['fecha_fin'],
            'estado' => false,
        ]);
    }

    public function obtenerTodas()
    {
        return Competencia::with(['areaNivel.areaOlimpiada.area', 'areaNivel.nivel', 'examenes'])->get();
    }

    public function obtenerPorId(int $id_competencia): ?Competencia
    {
        return Competencia::with(['areaNivel.areaOlimpiada.area', 'areaNivel.nivel', 'examenes'])->find($id_competencia);
    }

    public function obtenerPorAreaYNivel(int $id_area, int $id_nivel)
    {
        return Competencia::with(['areaNivel.areaOlimpiada.area', 'areaNivel.nivel', 'examenes'])
            ->whereHas('areaNivel', function ($query) use ($id_nivel) {
                $query->where('id_nivel', $id_nivel);
            })
            ->whereHas('areaNivel.areaOlimpiada', function ($query) use ($id_area) {
                $query->where('id_area', $id_area);
            })
            ->first();
    }

    public function obtenerPorAreaNivelId(int $id_area_nivel)
    {
        return Competencia::with(['areaNivel.areaOlimpiada.area', 'areaNivel.nivel', 'examenes'])
            ->where('id_area_nivel', $id_area_nivel)
            ->get();
    }
}