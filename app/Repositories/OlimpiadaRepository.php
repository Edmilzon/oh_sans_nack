<?php

namespace App\Repositories;

use App\Model\Olimpiada;
use Illuminate\Database\Eloquent\Collection;

class OlimpiadaRepository
{
    protected $model;

    public function __construct(Olimpiada $olimpiada)
    {
        $this->model = $olimpiada;
    }

    public function obtenerOlimpiadasAnteriores($gestionActual): Collection
    {
        return $this->model->where('gestion', '!=', $gestionActual)
                          ->orderBy('gestion', 'desc')
                          ->get();
    }

    public function obtenerOlimpiadaActual($gestionActual): ?Olimpiada
    {
        return $this->model->where('gestion', $gestionActual)->first();
    }

    public function existeOlimpiadaActual($gestionActual): bool
    {
        return $this->model->where('gestion', $gestionActual)->exists();
    }

    public function firstOrCreate(array $attributes, array $values = []): Olimpiada
    {
        return $this->model->firstOrCreate($attributes, $values);
    }

    public function obtenerGestiones(): Collection
    {
        return $this->model->orderBy('gestion', 'desc')->get();
    }
}