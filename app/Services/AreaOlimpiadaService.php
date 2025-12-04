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

    public function getAreasByOlimpiada(int|string $identifier): Collection
    {
        if (is_numeric($identifier) && strlen((string)$identifier) === 4) {
            return $this->areaOlimpiadaRepository->findAreasByGestion((string) $identifier);
        }

        return $this->areaOlimpiadaRepository->findAreasByOlimpiadaId((int) $identifier);
    }

    public function getAreasGestionActual()
    {
        $gestionActual = date('Y');
        return $this->areaOlimpiadaRepository->findAreasByGestionN($gestionActual);
    }

    public function getNombresAreasGestionActual()
    {
    $areas = $this->getAreasGestionActual();
    return $areas->pluck('nombre', 'id_area');
    }

    public function getAreasByGestion(string $gestion): Collection
    {
        return $this->areaOlimpiadaRepository->findAreasByGestion($gestion);
    }
}
