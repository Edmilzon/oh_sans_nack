<?php

namespace App\Services;

use App\Repositories\AreaOlimpiadaRepository;
use Illuminate\Support\Collection;

class AreaOlimpiadaService
{
    protected $areaOlimpiadaRepository;

    public function __construct(AreaOlimpiadaRepository $areaOlimpiadaRepository)
    {
        $this->areaOlimpiadaRepository = $areaOlimpiadaRepository;
    }

    /**
     * Obtiene las áreas para una olimpiada dada, identificada por ID o gestión.
     *
     * @param int|string $identifier ID de la olimpiada o año de gestión (ej: 2025).
     * @return Collection
     */
    public function getAreasByOlimpiada(int|string $identifier): Collection
    {
        // Si el identificador es numérico y tiene 4 dígitos, asumimos que es una gestión.
        // El Repositorio se encarga de usar 'gestion_olimp'.
        if (is_numeric($identifier) && strlen((string)$identifier) === 4) {
            return $this->areaOlimpiadaRepository->findAreasByGestion((string) $identifier);
        }

        return $this->areaOlimpiadaRepository->findAreasByOlimpiadaId((int) $identifier);
    }

    /**
     * Obtiene las áreas asociadas a la gestión actual (año actual).
     */
    public function getAreasGestionActual(): Collection
    {
        $gestionActual = date('Y');
        // El Repositorio ya se encarga de los joins y los nombres de columna (gestion_olimp, nombre_area)
        return $this->areaOlimpiadaRepository->findAreasByGestionN($gestionActual);
    }

    /**
     * Obtiene un mapa (ID => Nombre) de las áreas de la gestión actual.
     */
    public function getNombresAreasGestionActual(): Collection
    {
        $areas = $this->getAreasGestionActual();
        // El Repositorio garantiza que el resultado tiene las claves 'id_area' y 'nombre' (gracias al alias)
        return $areas->pluck('nombre', 'id_area');
    }

    /**
     * Obtiene las áreas para una gestión específica.
     */
    public function getAreasByGestion(string $gestion): Collection
    {
        return $this->areaOlimpiadaRepository->findAreasByGestion($gestion);
    }
}
