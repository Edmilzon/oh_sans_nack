<?php

namespace App\Repositories;

use App\Model\RolAccion;
use Illuminate\Database\Eloquent\Collection;

class RolAccionRepository
{
    public function getByRol(int $idRol): Collection
    {
        return RolAccion::with(['accionSistema'])
            ->where('id_rol', $idRol)
            ->get();
    }

    public function asignarAccion(int $idRol, int $idAccion): RolAccion
    {
        return RolAccion::updateOrCreate(
            ['id_rol' => $idRol, 'id_accion' => $idAccion],
            ['activo' => true]
        );
    }

    public function eliminarAccion(int $idRol, int $idAccion): bool
    {
        return RolAccion::where('id_rol', $idRol)
            ->where('id_accion', $idAccion)
            ->delete();
    }

    public function eliminarTodasPorRol(int $idRol): void
    {
        RolAccion::where('id_rol', $idRol)->delete();
    }
}
