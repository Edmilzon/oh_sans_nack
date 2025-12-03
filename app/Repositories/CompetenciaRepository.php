<?php

namespace App\Repositories;

use App\Model\Competencia;

class CompetenciaRepository
{
    /**
     * Crea una nueva competencia en la base de datos.
     *
     * @param array $data
     * @return Competencia
     */
    public function crear(array $data): Competencia
    {
        return Competencia::create([
            'id_area_nivel' => $data['id_area_nivel'],
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin' => $data['fecha_fin'],
            'estado' => false, // Por defecto, una competencia nueva no está activa para evaluación
        ]);
    }

    /**
     * Obtiene todas las competencias con sus relaciones.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function obtenerTodas()
    {
        return Competencia::with(['areaNivel.areaOlimpiada.area', 'areaNivel.nivel', 'examenes'])->get();
    }

    /**
     * Obtiene una competencia por su ID con sus relaciones.
     *
     * @param int $id_competencia
     * @return Competencia|null
     */
    public function obtenerPorId(int $id_competencia): ?Competencia
    {
        return Competencia::with(['areaNivel.areaOlimpiada.area', 'areaNivel.nivel', 'examenes'])->find($id_competencia);
    }
}