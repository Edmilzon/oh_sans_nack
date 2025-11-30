<?php

namespace App\Repositories;

use App\Model\Usuario;
use Illuminate\Database\Eloquent\Builder;

class UsuarioRepository
{
    /**
     * Busca un usuario por su dirección de email (para login).
     *
     * @param string $email
     * @return Usuario|null
     */
    public function findByEmail(string $email): ?Usuario
    {
        // Columna corregida: email -> email_usuario
        return Usuario::where('email_usuario', $email)
            // Necesitamos la persona para nombre/apellido y roles para el proceso de login
            ->with(['persona', 'roles'])
            ->first();
    }

    /**
     * Busca un usuario por su CI (en tabla Persona) y carga todas sus relaciones detalladas.
     *
     * @param string $ci
     * @return Usuario|null
     */
    public function findByCiWithDetails(string $ci): ?Usuario
    {
        // La búsqueda por CI debe hacerse a través de la tabla 'persona'
        return Usuario::whereHas('persona', function (Builder $query) use ($ci) {
            $query->where('ci_pers', $ci); // Columna corregida
        })
        ->with([
            'persona',
            // Carga los roles (columna corregida en el modelo: nombre_rol)
            'roles',

            // Carga las áreas de las que es responsable
            // Ruta corregida: ResponsableArea -> AreaOlimpiada -> Area/Olimpiada
            'responsableArea.areaOlimpiada.area',
            'responsableArea.areaOlimpiada.olimpiada',

            // Carga las asignaciones de evaluador
            // Ruta corregida: EvaluadorAn -> AreaNivel -> AreaOlimpiada -> Area
            'evaluadorAn.areaNivel.areaOlimpiada.area',
            'evaluadorAn.areaNivel.nivel',
            // La relación 'gradoEscolaridad' en AreaNivel ya no existe directamente
            // Si la necesitas, debe buscarse a través de NivelGrado
            // 'evaluadorAn.areaNivel.gradoEscolaridad', // Relación eliminada/corregida
        ])->first();
    }
}
