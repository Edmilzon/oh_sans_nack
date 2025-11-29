<?php

namespace App\Services;

use App\Model\Olimpiada;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\OlimpiadaRepository;

class OlimpiadaService
{
    protected $olimpiadaRepository;

    public function __construct(OlimpiadaRepository $olimpiadaRepository)
    {
        $this->olimpiadaRepository = $olimpiadaRepository;
    }

    public function obtenerGestiones()
    {
        $gestiones = $this->olimpiadaRepository->obtenerGestiones();
        $currentYear = date('Y');

        return $gestiones->map(function ($olimpiada) use ($currentYear) {
            return [
                'id' => $olimpiada->id_olimpiada,
                'nombre' => $olimpiada->nombre,
                'gestion' => $olimpiada->gestion,
                'esActual' => $olimpiada->gestion == $currentYear,
            ];
        });
    }

    public function obtenerOlimpiadaActual(): Olimpiada
    {
        $gestionActual = date('Y');
        $nombreOlimpiada = "Olimpiada Científica Estudiantil $gestionActual";
        
        return Olimpiada::firstOrCreate(
            ['gestion' => $gestionActual],
            ['nombre' => $nombreOlimpiada]
        );
    }

    public function obtenerOlimpiadaPorGestion($gestion): Olimpiada
    {
        $nombreOlimpiada = "Olimpiada Científica Estudiantil $gestion";
        
        return Olimpiada::firstOrCreate(
            ['gestion' => $gestion],
            ['nombre' => $nombreOlimpiada]
        );
    }

    public function existeOlimpiadaActual(): bool
    {
        $gestionActual = date('Y');
        return Olimpiada::where('gestion', $gestionActual)->exists();
    }

    public function obtenerOlimpiadasAnteriores(): Collection
    {
        $gestionActual = date('Y');
        return $this->olimpiadaRepository->obtenerOlimpiadasAnteriores($gestionActual);
    }
}