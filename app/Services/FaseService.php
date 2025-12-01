<?php

namespace App\Services;

use App\Repositories\FaseRepository;
// CORRECCIÓN: Usamos Support\Collection
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FaseService
{
    public function __construct(
        protected FaseRepository $repo
    ) {}

    // ... (Métodos anteriores de Fases Globales sin cambios) ...
    public function obtenerFasesGlobales(): Collection { return $this->repo->obtenerFasesGlobales(); }
    public function listarAccionesSistema(): Collection { return $this->repo->listarAccionesSistema(); }
    public function getConfiguracionAccionesPorGestion(int $id) { return $this->repo->getConfiguracionMatriz($id); }
    public function guardarConfiguracionAcciones(int $id, array $data) {
        DB::transaction(fn() => $this->repo->guardarConfiguracion($data));
    }
    public function actualizarAccionHabilitada(int $idG, int $idF, int $idA, bool $hab) {
        $this->repo->actualizarAccionUnica($idF, $idA, $hab);
    }

    // ... (Métodos de Competencia CRUD) ...
    public function obtenerFasesPorAreaNivel(int $id): Collection { return $this->repo->obtenerPorAreaNivel($id); }
    public function crearFase(array $data, int $id) { return $this->repo->crearCompetencia($data, $id); }
    public function actualizarFase(int $id, array $data) {
        $comp = $this->repo->findCompetenciaById($id);
        if ($comp) { $this->repo->updateCompetencia($comp, $data); return $comp->fresh(); }
        return null;
    }
    public function eliminarFase(int $id) {
        $comp = $this->repo->findCompetenciaById($id);
        return $comp ? $this->repo->deleteCompetencia($comp) : false;
    }

    // --- MÉTODO NUEVO PARA CAMBIAR ESTADO ---
    public function cambiarEstadoFase(int $idFase, string $nuevoEstado)
    {
        // Mapeo: "EN_EVALUACION" -> true, cualquier otro -> false
        $estadoBooleano = ($nuevoEstado === 'EN_EVALUACION');
        return $this->repo->actualizarEstadoCompetencia($idFase, $estadoBooleano);
    }

    // --- MÉTODO REPORTE (CORREGIDO TIPO) ---
    public function getSubFasesDetails(int $idArea, int $idNivel, int $idOlimpiada): Collection
    {
        return $this->repo->getSubFasesDetails($idArea, $idNivel, $idOlimpiada);
    }
}
