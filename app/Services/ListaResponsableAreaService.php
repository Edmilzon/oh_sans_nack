<?php

namespace App\Services;

use App\Repositories\ListaResponsableAreaRepository;
use Illuminate\Support\Collection;

class ListaResponsableAreaService
{
    protected ListaResponsableAreaRepository $listaResponsableAreaRepository;

    public function __construct(ListaResponsableAreaRepository $listaResponsableAreaRepository)
    {
        $this->listaResponsableAreaRepository = $listaResponsableAreaRepository;
    }

    public function getNivelesPorArea(int $idArea): Collection
    {
        if ($idArea <= 0) {
            return collect();
        }

        return $this->listaResponsableAreaRepository->getNivelesByArea($idArea);
    }

    public function getAreaPorResponsable(int $idResponsable): Collection
    {
        if ($idResponsable <= 0) {
            return collect();
        }

        return $this->listaResponsableAreaRepository->getAreaPorResponsable($idResponsable);
    }

  public function listarPorAreaYNivel(
    int $idResponsable,
    ?int $idArea,
    ?int $idNivel,
    ?int $idGrado,
    ?string $genero = null,
    ?string $departamento = null
): Collection {
    return $this->listaResponsableAreaRepository->listarPorAreaYNivel(
        $idResponsable,
        $idArea,
        $idNivel,
        $idGrado,
        $genero,
        $departamento
    );
}


   public function getListaGrados(?int $idArea, int $idNivel): Collection
    {
        if ($idNivel <= 0) {
            return collect();
        }

        if ($idArea !== null && $idArea > 0) {
            return $this->listaResponsableAreaRepository->getListaGradosPorAreaNivel($idArea, $idNivel);
        }

        return $this->listaResponsableAreaRepository->getListaGradosPorNivel($idNivel);
    }


   public function getListaDepartamento(){
    return $this->listaResponsableAreaRepository->getListaDepartamento();
} 
   public function getListaGeneros(): array
{
    return $this->listaResponsableAreaRepository->getListaGeneros();
}

    public function getCompetidoresPorAreaYNivel(int $idArea, int $idNivel): Collection
    {
        return $this->listaResponsableAreaRepository->getCompetidoresPorAreaYNivel($idArea, $idNivel);
    }

}
