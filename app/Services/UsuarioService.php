<?php

namespace App\Services;

use App\Repositories\UsuarioRepository;
use Illuminate\Support\Facades\Hash;
use App\Model\Olimpiada; // Aseguramos importación

class UsuarioService
{
    protected $usuarioRepository;

    public function __construct(UsuarioRepository $usuarioRepository)
    {
        $this->usuarioRepository = $usuarioRepository;
    }

    /**
     * Autentica un usuario y genera un token de acceso.
     */
    public function login(array $credentials): ?array
    {
        $usuario = $this->usuarioRepository->findByEmail($credentials['email']);

        if (!$usuario || !Hash::check($credentials['password'], $usuario->password)) {
            return null;
        }

        $usuario->tokens()->delete();
        $token = $usuario->createToken('auth_token')->plainTextToken;
        $roles = $usuario->roles->pluck('nombre');

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id_usuario' => $usuario->id_usuario,
                'nombre' => $usuario->persona->nombre ?? '',
                'apellido' => $usuario->persona->apellido ?? '',
                'email' => $usuario->email,
                'roles' => $roles,
            ]
        ];
    }

    /**
     * Obtiene la información detallada de un usuario por su CI.
     */
    public function getUsuarioDetalladoPorCi(string $ci): ?array
    {
        $usuario = $this->usuarioRepository->findByCiWithDetails($ci);

        if (!$usuario) {
            return null;
        }

        // Agrupación por gestión (ID Olimpiada)
        $rolesPorGestion = $usuario->roles->groupBy(function ($rol) {
            return $rol->pivot->id_olimpiada;
        })->map(function ($roles, $idOlimpiada) use ($usuario) {

            // Obtener nombre de la gestión
            $gestionNombre = "Desconocida";
            $olimpiada = Olimpiada::find($idOlimpiada); // O usar cache si es frecuente
            if ($olimpiada) {
                $gestionNombre = $olimpiada->gestion; // Ej: "2025"
            }

            // Mapear Roles dentro de esa gestión
            $rolesFormatted = $roles->map(function ($rol) use ($usuario, $idOlimpiada) {
                $detalles = null;
                $rolName = $rol->nombre; // Ej: "Evaluador"

                // CASO 1: RESPONSABLE DE ÁREA
                if ($rolName === 'Responsable Area' || $rolName === 'Responsable de area') {
                    $areas = $usuario->responsableAreas
                        ->filter(fn ($ra) => $ra->areaOlimpiada && $ra->areaOlimpiada->id_olimpiada == $idOlimpiada)
                        ->map(fn ($ra) => [
                            'id_area' => $ra->areaOlimpiada->area->id_area,
                            'nombre_area' => $ra->areaOlimpiada->area->nombre,
                        ])->values();

                    if ($areas->isNotEmpty()) {
                        $detalles = ['areas_responsable' => $areas];
                    }
                }

                // CASO 2: EVALUADOR (Ajustado a tu JSON)
                elseif ($rolName === 'Evaluador') {
                    $asignaciones = $usuario->evaluadoresAn
                        ->filter(fn ($ea) =>
                            $ea->areaNivel &&
                            $ea->areaNivel->areaOlimpiada &&
                            $ea->areaNivel->areaOlimpiada->id_olimpiada == $idOlimpiada
                        )
                        ->map(function ($ea) {
                            // Tu JSON muestra "nombre_grado" en singular.
                            // Si hay varios grados, los unimos por comas.
                            $nombresGrados = $ea->areaNivel->gradosEscolaridad->pluck('nombre')->join(', ');

                            return [
                                'id_area_nivel' => $ea->areaNivel->id_area_nivel,
                                'nombre_area'   => $ea->areaNivel->areaOlimpiada->area->nombre,
                                'nombre_nivel'  => $ea->areaNivel->nivel->nombre,
                                'nombre_grado'  => $nombresGrados ?: 'Sin grado específico'
                            ];
                        })->values();

                    $detalles = ['asignaciones_evaluador' => $asignaciones];
                }

                return [
                    'rol' => $rolName,
                    'detalles' => $detalles
                ];
            })->values();

            return [
                'id_olimpiada' => $idOlimpiada,
                'gestion'      => $gestionNombre,
                'roles'        => $rolesFormatted
            ];
        })->values(); // Resetear índices del array para que sea JSON Array []

        return [
            'id_usuario' => $usuario->id_usuario,
            'nombre'     => $usuario->persona->nombre,
            'apellido'   => $usuario->persona->apellido,
            'ci'         => $usuario->persona->ci,
            'email'      => $usuario->email,
            'telefono'   => $usuario->persona->telefono,
            'created_at' => $usuario->created_at,
            'updated_at' => $usuario->updated_at,
            'roles_por_gestion' => $rolesPorGestion
        ];
    }
}
