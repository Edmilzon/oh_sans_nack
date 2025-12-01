<?php

namespace App\Services;

use App\Model\Olimpiada;
use App\Repositories\OlimpiadaRepository;
use Illuminate\Support\Collection;
use Exception;

class OlimpiadaService
{
    public function __construct(
        protected OlimpiadaRepository $olimpiadaRepository
    ) {}

    /**
     * Obtiene la olimpiada de la gestión actual o la crea si no existe.
     * (El service ya no usa Olimpiada::firstOrCreate())
     */
    public function obtenerOlimpiadaActual(): Olimpiada
    {
        $gestionActual = date('Y');
        $nombreOlimpiada = "Olimpiada Científica Estudiantil $gestionActual";

        return $this->olimpiadaRepository->firstOrCreate(
            ['gestion' => $gestionActual],
            ['nombre' => $nombreOlimpiada, 'estado' => true]
        );
    }

    /**
     * Obtiene una olimpiada para una gestión específica, creándola si no existe.
     * (El service ya no usa Olimpiada::firstOrCreate())
     */
    public function obtenerOlimpiadaPorGestion(string $gestion): Olimpiada
    {
        $nombreOlimpiada = "Olimpiada Científica Estudiantil $gestion";

        return $this->olimpiadaRepository->firstOrCreate(
            ['gestion' => $gestion],
            ['nombre' => $nombreOlimpiada, 'estado' => false]
        );
    }

    /**
     * Obtiene las olimpiadas anteriores a la gestión actual.
     */
    public function obtenerOlimpiadasAnteriores(): Collection
    {
        $gestionActual = date('Y');
        return $this->olimpiadaRepository->getAnteriores($gestionActual);
    }

    /**
     * Obtiene todas las gestiones de olimpiadas y las formatea.
     */
    public function obtenerGestiones(): Collection
    {
        $gestiones = $this->olimpiadaRepository->obtenerGestiones();
        $currentYear = date('Y');

        return $gestiones->map(function ($olimpiada) use ($currentYear) {
            return [
                'id' => $olimpiada->id_olimpiada,
                'nombre' => $olimpiada->nombre,
                'gestion' => $olimpiada->gestion,
                'esActual' => (string)$olimpiada->gestion === $currentYear,
            ];
        });
    }
}
