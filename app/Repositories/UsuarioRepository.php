<?php

namespace App\Repositories;

use App\Model\Usuario;

class UsuarioRepository
{
    public function findByEmail(string $email): ?Usuario
    {
        return Usuario::where('email', $email)->with('roles')->first();
    }

    public function findByCiWithDetails(string $ci): ?Usuario
    {
        return Usuario::whereHas('persona', function ($q) use ($ci) {

                $q->where('ci', $ci);
            })
            ->with([
                'persona',
                'roles',

                'responsableAreas.areaOlimpiada.area',
                'responsableAreas.areaOlimpiada.olimpiada',

                'evaluadoresAn.areaNivel.areaOlimpiada.area',
                'evaluadoresAn.areaNivel.nivel',
                'evaluadoresAn.areaNivel.gradosEscolaridad',
            ])->first();
    }
}
