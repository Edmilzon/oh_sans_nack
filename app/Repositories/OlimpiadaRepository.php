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

     public function create(array $data): Olimpiada
    {
        return $this->model->create(array_merge($data, ['estado' => false]));
    }

    public function find(int $id): ?Olimpiada
    {
        return $this->model->find($id);
    }

    public function desactivarTodas(): void
    {
        $this->model->query()->update(['estado' => false]);
    }

    public function activar(int $id): bool
    {
        return $this->model->where('id_olimpiada', $id)->update(['estado' => true]);
    }

    public function update(int $id, array $data): bool
    {
        return $this->model->where('id_olimpiada', $id)->update($data);
    }
}
