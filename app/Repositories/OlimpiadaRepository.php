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

    /**
     * Obtiene las olimpiadas cuya gestión es diferente a la gestión actual.
     * Columna corregida: gestion -> gestion_olimp
     */
    public function obtenerOlimpiadasAnteriores($gestionActual): Collection
    {
        return $this->model->where('gestion_olimp', '!=', $gestionActual)
                            ->orderBy('gestion_olimp', 'desc')
                            ->get();
    }

    /**
     * Obtiene la olimpiada correspondiente a la gestión actual.
     * Columna corregida: gestion -> gestion_olimp
     */
    public function obtenerOlimpiadaActual($gestionActual): ?Olimpiada
    {
        return $this->model->where('gestion_olimp', $gestionActual)->first();
    }

    /**
     * Verifica si existe una olimpiada con la gestión actual.
     * Columna corregida: gestion -> gestion_olimp
     */
    public function existeOlimpiadaActual($gestionActual): bool
    {
        return $this->model->where('gestion_olimp', $gestionActual)->exists();
    }

    /**
     * Busca o crea la olimpiada.
     */
    public function firstOrCreate(array $attributes, array $values = []): Olimpiada
    {
        // NOTA: Asegurarse de que los $attributes usen 'gestion_olimp' si están basados en 'gestion'.
        // Ejemplo: Si el input es ['gestion' => '2025'], debe ser mapeado a ['gestion_olimp' => '2025'] antes de llamar.
        return $this->model->firstOrCreate($attributes, $values);
    }

    /**
     * Obtiene todas las gestiones registradas.
     * Columna corregida: gestion -> gestion_olimp
     */
    public function obtenerGestiones(): Collection
    {
        return $this->model->orderBy('gestion_olimp', 'desc')->get();
    }
}
