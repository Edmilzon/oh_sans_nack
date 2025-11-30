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

    /**
     * Obtiene los niveles asignados a una área específica en la gestión actual.
     * @param int $idArea
     * @return Collection
     */
    public function getNivelesPorArea(int $idArea): Collection
    {
        if ($idArea <= 0) {
            return collect();
        }

        // El Repositorio se encarga de la navegación Area -> AreaOlimpiada -> Olimpiada
        return $this->listaResponsableAreaRepository->getNivelesByArea($idArea);
    }

    /**
     * Obtiene las áreas de las que es responsable un usuario.
     * @param int $idResponsable
     * @return Collection
     */
    public function getAreaPorResponsable(int $idResponsable): Collection
    {
        if ($idResponsable <= 0) {
            return collect();
        }

        return $this->listaResponsableAreaRepository->getAreaPorResponsable($idResponsable);
    }

    /**
     * Lista los competidores aplicando varios filtros (área, nivel, grado, género, departamento).
     * @return Collection
     */
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


    /**
     * Obtiene los grados de escolaridad permitidos para un nivel.
     * @param int $idNivel
     * @return Collection
     */
    public function getListaGrados(int $idNivel): Collection
    {
        if ($idNivel <= 0) {
            return collect();
        }

        return $this->listaResponsableAreaRepository->getListaGrados($idNivel);
    }


    /**
     * Obtiene el listado de departamentos (tabla 'departamento').
     * @return Collection
     */
    public function getListaDepartamento(): Collection
    {
        return $this->listaResponsableAreaRepository->getListaDepartamento();
    }

    /**
     * Obtiene el listado de géneros disponibles.
     * @return array
     */
    public function getListaGeneros(): array
    {
        return $this->listaResponsableAreaRepository->getListaGeneros();
    }

    /**
     * Obtiene la lista de competidores para una área y nivel específicos.
     * @param int $idArea
     * @param int $idNivel
     * @return Collection
     */
    public function getCompetidoresPorAreaYNivel(int $idArea, int $idNivel): Collection
    {
        return $this->listaResponsableAreaRepository->getCompetidoresPorAreaYNivel($idArea, $idNivel);
    }
}
