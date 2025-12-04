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

    public function obtenerOlimpiadaActual(): Olimpiada
    {
        $gestionActual = date('Y');
        $nombreOlimpiada = "Olimpiada Científica Estudiantil $gestionActual";

        return $this->olimpiadaRepository->firstOrCreate(
            ['gestion' => $gestionActual],
            ['nombre' => $nombreOlimpiada, 'estado' => true]
        );
    }

    public function obtenerOlimpiadaPorGestion(string $gestion): Olimpiada
    {
        $nombreOlimpiada = "Olimpiada Científica Estudiantil $gestion";

        return $this->olimpiadaRepository->firstOrCreate(
            ['gestion' => $gestion],
            ['nombre' => $nombreOlimpiada, 'estado' => false]
        );
    }

    public function obtenerOlimpiadasAnteriores(): Collection
    {
        $gestionActual = date('Y');
        return $this->olimpiadaRepository->getAnteriores($gestionActual);
    }

    public function obtenerGestiones(): Collection
    {
        $gestiones = $this->olimpiadaRepository->obtenerGestiones();
        $currentYear = date('Y');

        return $gestiones->map(function ($olimpiada) use ($currentYear) {
            return [
                'id' => $olimpiada->id_olimpiada,
                'nombre' => $olimpiada->nombre,
                'gestion' => $olimpiada->gestion,
                'esActual' => (string)$olimpiada->gestion === (string)$currentYear,
            ];
        });
    }
}
