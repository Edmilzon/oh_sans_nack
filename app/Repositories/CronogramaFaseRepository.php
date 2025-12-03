<?php

namespace App\Repositories;

use App\Model\CronogramaFase;
use Illuminate\Database\Eloquent\Collection;

class CronogramaFaseRepository
{
    public function getAll(): Collection
    {
        return CronogramaFase::with('faseGlobal')->get();
    }

    public function find(int $id): CronogramaFase
    {
        return CronogramaFase::findOrFail($id);
    }

    public function create(array $data): CronogramaFase
    {
        return CronogramaFase::create($data);
    }

    public function update(int $id, array $data): CronogramaFase
    {
        $cronograma = $this->find($id);
        $cronograma->update($data);
        return $cronograma;
    }

    public function delete(int $id): bool
    {
        $cronograma = $this->find($id);
        return $cronograma->delete();
    }

    /**
     * Devuelve los cronogramas filtrados por la Olimpiada padre de la Fase.
     * Incluye los datos de la Fase para que el frontend sepa de qué etapa es la fecha.
     */
    public function obtenerPorOlimpiada(int $idOlimpiada): Collection
    {
        return \App\Model\CronogramaFase::query()
            ->with(['faseGlobal' => function ($query) {
                $query->select('id_fase_global', 'nombre', 'codigo', 'orden');
            }])
            ->whereHas('faseGlobal', function ($query) use ($idOlimpiada) {
                $query->where('id_olimpiada', $idOlimpiada);
            })
            ->orderBy('fecha_inicio', 'asc')
            ->get();
    }
}
