<?php

namespace App\Services;

use App\Repositories\GradoEscolaridadRepository;
use App\Model\GradoEscolaridad;
use Illuminate\Database\Eloquent\Collection;

class GradoEscolaridadService
{
    protected $gradoEscolaridadRepository;

    public function __construct(GradoEscolaridadRepository $gradoEscolaridadRepository)
    {
        $this->gradoEscolaridadRepository = $gradoEscolaridadRepository;
    }

    public function getAll(): Collection
    {
        return $this->gradoEscolaridadRepository->getAll();
    }

    public function findById(int $id): ?GradoEscolaridad
    {
        return $this->gradoEscolaridadRepository->findById($id);
    }
}