<?php

namespace App\Services;

use App\Repositories\UsuarioRepository;
use Illuminate\Support\Facades\Hash;

class UsuarioService
{
    protected $usuarioRepository;

    public function __construct(UsuarioRepository $usuarioRepository)
    {
        $this->usuarioRepository = $usuarioRepository;
    }

    /**
     * Autentica un usuario y genera un token de acceso.
     *
     * @param array $credentials
     * @return array|null
     */
    public function login(array $credentials): ?array
    {
        $usuario = $this->usuarioRepository->findByEmail($credentials['email']);

        if (!$usuario || !Hash::check($credentials['password'], $usuario->password)) {
            return null; // Credenciales inválidas
        }

        // Elimina tokens antiguos para mantener la tabla limpia
        $usuario->tokens()->delete();

        // Crea un nuevo token que expira en 1 hora (configurado en config/sanctum.php)
        $token = $usuario->createToken('auth_token')->plainTextToken;

        // Obtiene una lista simple de los nombres de los roles del usuario.
        $roles = $usuario->roles->pluck('nombre');

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id_usuario' => $usuario->id_usuario,
                'nombre' => $usuario->nombre,
                'apellido' => $usuario->apellido,
                'email' => $usuario->email,
                'roles' => $roles,
            ]
        ];
    }

    /**
     * Obtiene la información detallada de un usuario por su CI.
     *
     * @param string $ci
     * @return array|null
     */
    public function getUsuarioDetalladoPorCi(string $ci): ?array
    {
        $usuario = $this->usuarioRepository->findByCiWithDetails($ci);

        if (!$usuario) {
            return null;
        }

        // Agrupa los roles y sus detalles por gestión de olimpiada
        $rolesPorGestion = $usuario->roles->groupBy('pivot.id_olimpiada')->map(function ($roles, $idOlimpiada) use ($usuario) {
            $olimpiada = $roles->first()->pivot->olimpiada;

            return [
                'id_olimpiada' => $idOlimpiada,
                'gestion' => $olimpiada->gestion,
                'roles' => $roles->map(function ($rol) use ($usuario, $idOlimpiada) {
                    $detalles = null;
                    if ($rol->nombre === 'Responsable Area') {
                        $detalles = [
                            'areas_responsable' => $usuario->responsableArea
                                ->where('areaOlimpiada.id_olimpiada', $idOlimpiada)
                                ->map(function ($ra) {
                                    return [
                                        'id_area' => $ra->areaOlimpiada->area->id_area,
                                        'nombre_area' => $ra->areaOlimpiada->area->nombre,
                                    ];
                                })->values()
                        ];
                    } elseif ($rol->nombre === 'Evaluador') {
                        $detalles = [
                            'asignaciones_evaluador' => $usuario->evaluadorAn
                                ->where('areaNivel.id_olimpiada', $idOlimpiada)
                                ->map(function ($ea) {
                                    return [
                                        'id_area_nivel' => $ea->areaNivel->id_area_nivel,
                                        'nombre_area' => $ea->areaNivel->area->nombre,
                                        'nombre_nivel' => $ea->areaNivel->nivel->nombre,
                                        'nombre_grado' => $ea->areaNivel->gradoEscolaridad->nombre,
                                    ];
                                })->values()
                        ];
                    }

                    return [
                        'rol' => $rol->nombre,
                        'detalles' => $detalles,
                    ];
                })
            ];
        })->values();

        return [
            'id_usuario' => $usuario->id_usuario,
            'nombre' => $usuario->nombre,
            'apellido' => $usuario->apellido,
            'ci' => $usuario->ci,
            'email' => $usuario->email,
            'telefono' => $usuario->telefono,
            'created_at' => $usuario->created_at,
            'updated_at' => $usuario->updated_at,
            'roles_por_gestion' => $rolesPorGestion,
        ];
    }
}
