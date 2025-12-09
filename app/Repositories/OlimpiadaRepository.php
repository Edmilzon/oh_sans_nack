<?php

namespace App\Repositories;

use App\Model\Olimpiada;
use Illuminate\Database\Eloquent\Collection;

class OlimpiadaRepository
{
    protected Olimpiada $model;

    public function __construct(Olimpiada $olimpiada)
    {
        $this->model = $olimpiada;
    }

    public function getAnteriores(string $gestionActual): Collection
    {
        return $this->model->where('gestion', '!=', $gestionActual)
                          ->orderBy('gestion', 'desc')
                          ->get();
    }

    public function obtenerGestiones(): Collection
    {
        return $this->model->orderBy('gestion', 'desc')->get();
    }

    public function firstOrCreate(array $attributes, array $values = []): Olimpiada
    {
        return $this->model->firstOrCreate($attributes, $values);
    }

    public function obtenerOlimpiadasAnteriores($gestionActual): Collection
    {
        return $this->model->where('gestion', '!=', $gestionActual)
                          ->orderBy('gestion', 'desc')
                          ->get();
    }

    public function obtenerMasReciente(): ?Olimpiada
    {
        return Olimpiada::orderBy('gestion', 'desc')
                        ->orderBy('id_olimpiada', 'desc')
                        ->first();
    }

    public function findActive(): ?Olimpiada
    {
        return Olimpiada::where('estado', true)
            ->orderBy('id_olimpiada', 'desc')
            ->first();
    }
}
