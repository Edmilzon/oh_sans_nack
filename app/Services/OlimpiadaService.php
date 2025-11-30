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

    /**
     * Obtiene todas las gestiones registradas y mapea la salida.
     * @return Collection
     */
    public function obtenerGestiones(): Collection
    {
        // El Repositorio ya ordena por gestion_olimp
        $gestiones = $this->olimpiadaRepository->obtenerGestiones();
        $currentYear = date('Y');

        return $gestiones->map(function ($olimpiada) use ($currentYear) {
            // Mapeo de columnas V8 a las claves de salida esperadas
            return [
                'id' => $olimpiada->id_olimpiada,
                'nombre' => $olimpiada->nombre_olimp, // Columna corregida
                'gestion' => $olimpiada->gestion_olimp, // Columna corregida
                'esActual' => $olimpiada->gestion_olimp == $currentYear, // Columna corregida
            ];
        });
    }

    /**
     * Obtiene o crea la olimpiada de la gestión actual.
     * @return Olimpiada
     */
    public function obtenerOlimpiadaActual(): Olimpiada
    {
        $gestionActual = date('Y');
        $nombreOlimpiada = "Olimpiada Científica Estudiantil $gestionActual";

        // Columnas corregidas para firstOrCreate
        return Olimpiada::firstOrCreate(
            ['gestion_olimp' => $gestionActual],
            ['nombre_olimp' => $nombreOlimpiada]
        );
    }

    /**
     * Obtiene o crea la olimpiada para una gestión específica.
     * @param string $gestion
     * @return Olimpiada
     */
    public function obtenerOlimpiadaPorGestion($gestion): Olimpiada
    {
        $nombreOlimpiada = "Olimpiada Científica Estudiantil $gestion";

        // Columnas corregidas para firstOrCreate
        return Olimpiada::firstOrCreate(
            ['gestion_olimp' => $gestion],
            ['nombre_olimp' => $nombreOlimpiada]
        );
    }

    /**
     * Verifica si existe la olimpiada de la gestión actual.
     * @return bool
     */
    public function existeOlimpiadaActual(): bool
    {
        $gestionActual = date('Y');
        // Columna corregida para where
        return Olimpiada::where('gestion_olimp', $gestionActual)->exists();
    }

    /**
     * Obtiene las olimpiadas de gestiones anteriores.
     * @return Collection
     */
    public function obtenerOlimpiadasAnteriores(): Collection
    {
        $gestionActual = date('Y');
        // El Repositorio ya usa gestion_olimp
        return $this->olimpiadaRepository->obtenerOlimpiadasAnteriores($gestionActual);
    }
}
