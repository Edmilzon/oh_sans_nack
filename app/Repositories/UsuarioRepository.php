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
                // Buscamos el CI en la tabla Persona, que es donde realmente está.
                $q->where('ci', $ci);
            })
            ->with([
                'persona',
                'roles',
                // CORREGIDO: Uso de la relación PLURAL 'responsableAreas' y carga de relaciones anidadas
                'responsableAreas.areaOlimpiada.area',
                'responsableAreas.areaOlimpiada.olimpiada',

                // Eager loading para Evaluador (también corregido para consistencia)
                'evaluadoresAn.areaNivel.areaOlimpiada.area',
                'evaluadoresAn.areaNivel.nivel',
                'evaluadoresAn.areaNivel.gradosEscolaridad', // Relación Many-to-Many
            ])->first();
    }
}
