<?php

namespace App\Services;

use App\Repositories\UsuarioRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;

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
        // El Repositorio carga Usuario con Persona y Roles.
        $usuario = $this->usuarioRepository->findByEmail($credentials['email']);

        // Columna corregida: password -> password_usuario
        if (!$usuario || !Hash::check($credentials['password'], $usuario->password_usuario)) {
            return null; // Credenciales inválidas
        }

        // Elimina tokens antiguos para mantener la tabla limpia
        $usuario->tokens()->delete();

        // Crea un nuevo token
        $token = $usuario->createToken('auth_token')->plainTextToken;

        // Obtiene una lista simple de los nombres de los roles del usuario.
        // Columna corregida: nombre -> nombre_rol
        $roles = $usuario->roles->pluck('nombre_rol');

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id_usuario' => $usuario->id_usuario,
                // Mapeo Persona/Usuario -> Frontend keys
                'nombre' => $usuario->persona->nombre_pers,
                'apellido' => $usuario->persona->apellido_pers,
                'email' => $usuario->email_usuario, // Columna corregida
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
        // El Repositorio busca por ci_pers y carga todas las relaciones
        $usuario = $this->usuarioRepository->findByCiWithDetails($ci);

        if (!$usuario) {
            return null;
        }

        // Agrupa los roles y sus detalles por gestión de olimpiada
        $rolesPorGestion = $usuario->roles->groupBy('pivot.id_olimpiada')->map(function ($roles, $idOlimpiada) use ($usuario) {

            // Acceso corregido a Olimpiada y Gestión
            $olimpiada = $roles->first()->pivot->olimpiada;

            return [
                'id_olimpiada' => $idOlimpiada,
                'gestion' => $olimpiada->gestion_olimp, // Columna corregida
                'roles' => $roles->map(function ($rol) use ($usuario, $idOlimpiada) {
                    $detalles = null;

                    // Columna corregida: nombre -> nombre_rol
                    if ($rol->nombre_rol === 'Responsable Area') {
                        $detalles = [
                            'areas_responsable' => $usuario->responsableArea
                                // Filtramos por la olimpiada actual en la relación anidada
                                ->filter(function($ra) use ($idOlimpiada) {
                                    return optional($ra->areaOlimpiada)->id_olimpiada === $idOlimpiada;
                                })
                                ->map(function ($ra) {
                                    $area = $ra->areaOlimpiada->area; // Navega al modelo Area
                                    return [
                                        'id_area' => $area->id_area,
                                        'nombre_area' => $area->nombre_area, // Columna corregida
                                    ];
                                })->values()
                        ];
                    }

                    // Columna corregida: nombre -> nombre_rol
                    elseif ($rol->nombre_rol === 'Evaluador') {
                        $detalles = [
                            'asignaciones_evaluador' => $usuario->evaluadorAn
                                // Filtramos por la olimpiada actual en la relación anidada
                                ->filter(function($ea) use ($idOlimpiada) {
                                    return optional($ea->areaNivel->areaOlimpiada)->id_olimpiada === $idOlimpiada;
                                })
                                ->map(function ($ea) {
                                    // Relaciones corregidas (AreaNivel -> AreaOlimpiada -> Area)
                                    $area = $ea->areaNivel->areaOlimpiada->area;
                                    $nivel = $ea->areaNivel->nivel;

                                    return [
                                        'id_area_nivel' => $ea->id_area_nivel, // Usamos la PK de EvaluadorAn o AreaNivel
                                        'nombre_area' => $area->nombre_area, // Columna corregida
                                        'nombre_nivel' => $nivel->nombre_nivel, // Columna corregida
                                        // NOTA: Se asume que grado se obtiene vía NivelGrado si es necesario,
                                        // pero aquí se devuelve null para evitar error, ya que no se cargó NivelGrado
                                        'nombre_grado' => null,
                                    ];
                                })->values()
                        ];
                    }

                    return [
                        'rol' => $rol->nombre_rol, // Columna corregida
                        'detalles' => $detalles,
                    ];
                })
            ];
        })->values();

        // Mapeo final de atributos de salida (Front)
        return [
            'id_usuario' => $usuario->id_usuario,
            'nombre' => $usuario->persona->nombre_pers, // Acceso corregido
            'apellido' => $usuario->persona->apellido_pers, // Acceso corregido
            'ci' => $usuario->persona->ci_pers, // Acceso corregido
            'email' => $usuario->email_usuario, // Columna corregida
            'telefono' => $usuario->persona->telefono_pers, // Acceso corregido
            'created_at' => $usuario->created_at,
            'updated_at' => $usuario->updated_at,
            'roles_por_gestion' => $rolesPorGestion,
        ];
    }
}
