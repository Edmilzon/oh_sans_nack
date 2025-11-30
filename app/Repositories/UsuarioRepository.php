<?php

namespace App\Repositories;

use App\Model\Usuario;

class UsuarioRepository
{
    /**
     * Busca un usuario por su dirección de email.
     *
     * @param string $email
     * @return Usuario|null
     */
    public function findByEmail(string $email): ?Usuario
    {
        return Usuario::where('email', $email)
            ->with('roles') // Carga solo los roles del usuario.
            ->first();
    }

    /**
     * Busca un usuario por su CI y carga todas sus relaciones detalladas.
     *
     * @param string $ci
     * @return Usuario|null
     */
    public function findByCiWithDetails(string $ci): ?Usuario
    {
        return Usuario::where('ci', $ci)
            ->with([
                // Carga los roles y la información del pivote (incluyendo id_olimpiada)
                'roles',
                // Carga las áreas de las que es responsable, navegando a través de las tablas intermedias
                'responsableArea.areaOlimpiada.area',
                'responsableArea.areaOlimpiada.olimpiada',
                // Carga las asignaciones de evaluador, con detalles de área, nivel y grado
                'evaluadorAn.areaNivel.area',
                'evaluadorAn.areaNivel.nivel',
                'evaluadorAn.areaNivel.gradoEscolaridad',
            ])->first();
    }
}
