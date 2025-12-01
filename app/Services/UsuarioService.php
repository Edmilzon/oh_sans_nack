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

        // Acceso correcto a datos personales
        $nombre = $usuario->persona->nombre;
        $apellido = $usuario->persona->apellido;
        $telefono = $usuario->persona->telefono;
        $ci_persona = $usuario->persona->ci;

        // Agrupar por el id_olimpiada del pivote
        $rolesPorGestion = $usuario->roles->groupBy(function ($rol) {
            return $rol->pivot->id_olimpiada;
        })->map(function ($roles, $idOlimpiada) use ($usuario) {

            // Buscar la gestión (Olimpiada) para obtener el nombre
            $gestionNombre = "Desconocida";
            $olimpiada = Olimpiada::find($idOlimpiada);
            if ($olimpiada) {
                $gestionNombre = $olimpiada->gestion;
            }

            return [
                'id_olimpiada' => $idOlimpiada,
                'gestion' => $gestionNombre,
                'roles' => $roles->map(function ($rol) use ($usuario, $idOlimpiada) {

                    $detalles = null;
                    $rolName = $rol->nombre;

                    // Lógica para Responsable de Área
                    if ($rolName === 'Responsable de area' || $rolName === 'Responsable Area') {
                        // CORREGIDO: Usamos la relación PLURAL 'responsableAreas' del modelo Usuario
                        $areas = $usuario->responsableAreas
                            // Filtramos las áreas de esta gestión usando la relación cargada
                            ->filter(fn ($ra) => $ra->areaOlimpiada && $ra->areaOlimpiada->id_olimpiada == $idOlimpiada)
                            ->map(function ($ra) {
                                return [
                                    'id_area' => $ra->areaOlimpiada->area->id_area,
                                    'nombre_area' => $ra->areaOlimpiada->area->nombre,
                                ];
                            })->values();

                        // Devolvemos la estructura de detalles solo si hay áreas asignadas
                        if ($areas->isNotEmpty()) {
                            $detalles = ['areas_responsable' => $areas];
                        }
                    }
                    // Lógica para Evaluador
                    elseif ($rolName === 'Evaluador') {
                        $asignaciones = $usuario->evaluadoresAn
                            // Filtramos las asignaciones que pertenecen a esta olimpiada
                            ->filter(function ($ea) use ($idOlimpiada) {
                                return $ea->areaNivel
                                    && $ea->areaNivel->areaOlimpiada
                                    && $ea->areaNivel->areaOlimpiada->id_olimpiada == $idOlimpiada;
                            })
                            ->map(function ($ea) {
                                // Concatenar grados (Many-to-Many)
                                $nombresGrados = $ea->areaNivel->gradosEscolaridad->pluck('nombre')->join(', ');

                                return [
                                    'id_area_nivel' => $ea->areaNivel->id_area_nivel,
                                    'nombre_area' => $ea->areaNivel->areaOlimpiada->area->nombre,
                                    'nombre_nivel' => $ea->areaNivel->nivel->nombre,
                                    'nombre_grado' => $nombresGrados ?: 'N/A',
                                ];
                            })->values();

                        $detalles = ['asignaciones_evaluador' => $asignaciones];
                    }

                    return [
                        'rol' => $rolName,
                        'detalles' => $detalles,
                    ];
                })->values()
            ];
        })->values();

        // Estructura EXACTA del JSON que espera el frontend
        return [
            'id_usuario' => $usuario->id_usuario,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'ci' => $ci_persona,
            'email' => $usuario->email,
            'telefono' => $telefono,
            'created_at' => $usuario->created_at,
            'updated_at' => $usuario->updated_at,
            'roles_por_gestion' => $rolesPorGestion,
        ];
    }
}
