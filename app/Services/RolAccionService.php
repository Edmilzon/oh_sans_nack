<?php

namespace App\Services;

use App\Repositories\RolAccionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class RolAccionService
{
    protected $rolAccionRepository;

    public function __construct(RolAccionRepository $rolAccionRepository)
    {
        $this->rolAccionRepository = $rolAccionRepository;
    }

    public function obtenerAccionesPorRol(int $idRol): array
    {
        $rolAccion = $this->rolAccionRepository->getByRol($idRol);

        // Formateamos la respuesta para que sea más limpia
        return $rolAccion->map(function ($item) {
            return [
                'id_rol_accion' => $item->id_rol_accion,
                'id_rol' => $item->id_rol,
                'accion' => [
                    'id_accion' => $item->accionSistema->id_accion,
                    'codigo' => $item->accionSistema->codigo,
                    'nombre' => $item->accionSistema->nombre,
                ],
                'activo' => (bool)$item->activo
            ];
        })->toArray();
    }

    public function sincronizarAcciones(int $idRol, array $accionesIds): array
    {
        return DB::transaction(function () use ($idRol, $accionesIds) {
            // Opcional: Limpiar anteriores si se desea un "sync" estricto
            // $this->rolAccionRepository->eliminarTodasPorRol($idRol);

            $resultados = [];
            foreach ($accionesIds as $idAccion) {
                $resultados[] = $this->rolAccionRepository->asignarAccion($idRol, $idAccion);
            }

            return [
                'asignadas' => count($resultados),
                'detalle' => $resultados
            ];
        });
    }

    public function revocarAccion(int $idRol, int $idAccion): bool
    {
        return DB::transaction(function () use ($idRol, $idAccion) {
            return $this->rolAccionRepository->eliminarAccion($idRol, $idAccion);
        });
    }
}
