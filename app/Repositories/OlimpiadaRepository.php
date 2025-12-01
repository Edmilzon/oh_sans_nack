<?php

namespace App\Repositories;

use App\Model\Olimpiada;
use Illuminate\Database\Eloquent\Collection;

class OlimpiadaRepository
{
    protected Olimpiada $model;

    public function __construct(Olimpiada $olimpiada)
    {
        // Inyección de dependencia del modelo (DIP)
        $this->model = $olimpiada;
    }

    public function getAnteriores(string $gestionActual): Collection
    {
        return $this->model->where('gestion', '!=', $gestionActual)
                          ->orderBy('gestion', 'desc')
                          ->get();
    }

    /**
     * Obtiene todas las olimpiadas ordenadas por gestión.
     */
    public function obtenerGestiones(): Collection
    {
        return $this->model->orderBy('gestion', 'desc')->get();
    }

    /**
     * Encuentra una olimpiada por atributos o la crea si no existe.
     */
    public function firstOrCreate(array $attributes, array $values = []): Olimpiada
    {
        // Delegamos la lógica de persistencia al modelo (encapsulado)
        return $this->model->firstOrCreate($attributes, $values);
    }
}
