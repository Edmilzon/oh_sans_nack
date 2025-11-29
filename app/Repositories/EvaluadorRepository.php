<?php

namespace App\Repositories;

use App\Model\Usuario;
use App\Model\EvaluadorAn;
use App\Model\Rol;
use App\Model\AreaNivel;
use App\Model\Area;
use App\Model\AreaOlimpiada;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class EvaluadorRepository
{
    /**
     * Crea un nuevo usuario.
     *
     * @param array $data
     * @return Usuario
     */
    public function createUsuario(array $data): Usuario
    {
        $usuarioData = [
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
            'ci' => $data['ci'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']), 
            'telefono' => $data['telefono'] ?? null,
        ];

        return Usuario::create($usuarioData);
    }

    /**
     * Asigna el rol de "Evaluador" al usuario.
     *
     * @param Usuario $usuario
     * @param int $olimpiadaId
     * @return void
     */
    public function assignEvaluadorRole(Usuario $usuario, int $olimpiadaId): void
    {
        $rolEvaluador = Rol::where('nombre', 'Evaluador')->first();

        if (!$rolEvaluador) {
            throw new \Exception('El rol "Evaluador" no existe en el sistema');
        }

        $usuario->asignarRol('Evaluador', $olimpiadaId);
    }

    /**
     * Crea las relaciones entre el evaluador y las áreas.
     *
     * @param Usuario $usuario
     * @param array $areaNivelIds
     * @param int $olimpiadaId
     * @return array
     */
    public function createEvaluadorAreaRelations(Usuario $usuario, array $areaNivelIds, int $olimpiadaId): array
    {
        $evaluadorAreas = [];

        foreach ($areaNivelIds as $areaNivelId) {
            $evaluadorArea = EvaluadorAn::create([
                'id_usuario' => $usuario->id_usuario,
                'id_area_nivel' => $areaNivelId,
            ]);

            $evaluadorAreas[] = $evaluadorArea->load('areaNivel.area', 'areaNivel.nivel');
        }

        return $evaluadorAreas;
    }

    /**
     * Añade nuevas relaciones entre el evaluador y las áreas, evitando duplicados.
     *
     * @param Usuario $usuario
     * @param array $areaNivelIds
     * @param int $olimpiadaId
     * @return void
     */
    public function addEvaluadorAreaRelations(Usuario $usuario, array $areaNivelIds, int $olimpiadaId): void
    {
        foreach ($areaNivelIds as $areaNivelId) {
            // Usamos firstOrCreate para no crear duplicados si la relación ya existe.
            EvaluadorAn::firstOrCreate(
                [
                    'id_usuario' => $usuario->id_usuario,
                    'id_area_nivel' => $areaNivelId,
                ]
            );
        }

        if (!$usuario->tieneRol('Evaluador', $olimpiadaId)) {
            $this->assignEvaluadorRole($usuario, $olimpiadaId);
        }
    }

    /**
     * Añade nuevas relaciones entre el evaluador y las asignaciones de área/nivel, evitando duplicados.
     *
     * @param Usuario $usuario
     * @param array $areaNivelIds
     * @param int $olimpiadaId
     * @return void
     */
    public function addEvaluadorAreaNivelRelations(Usuario $usuario, array $areaNivelIds, int $olimpiadaId): void
    {
        foreach ($areaNivelIds as $areaNivelId) {
            EvaluadorAn::firstOrCreate(
                [
                    'id_usuario' => $usuario->id_usuario,
                    'id_area_nivel' => $areaNivelId,
                ]
            );
        }

        if (!$usuario->tieneRol('Evaluador', $olimpiadaId)) {
            $this->assignEvaluadorRole($usuario, $olimpiadaId);
        }
    }
    /**
     * Obtiene todos los evaluadores con sus áreas asignadas.
     *
     * @return array
     */
    public function getAllEvaluadoresWithAreas(): array
    {
        $evaluadores = Usuario::whereHas('roles', function ($query) {
            $query->where('nombre', 'Evaluador');
        })
        ->with(['evaluadorAn.area', 'roles'])
        ->get();

        return $evaluadores->map(function ($usuario) {
            return [
                'id_usuario' => $usuario->id_usuario,
                'nombre' => $usuario->nombre,
                'apellido' => $usuario->apellido,
                'ci' => $usuario->ci,
                'email' => $usuario->email,
                'telefono' => $usuario->telefono,
                'areas_asignadas' => $usuario->evaluadorAn->map(function ($ra) {
                    return [
                        'id_area' => $ra->area->id_area,
                        'nombre_area' => $ra->area->nombre
                    ];
                }),
                'olimpiadas' => $usuario->roles->map(function ($role) {
                    return [
                        'id_olimpiada' => $role->pivot->id_olimpiada,
                        'rol' => $role->nombre
                    ];
                }),
                'created_at' => $usuario->created_at,
                'updated_at' => $usuario->updated_at
            ];
        })->toArray();
    }

    /**
     * Obtiene un evaluador específico por ID con sus áreas.
     *
     * @param int $id
     * @return array|null
     */
    public function getEvaluadorByIdWithAreas(int $id): ?array
    {
        $usuario = Usuario::whereHas('roles', function ($query) {
            $query->where('nombre', 'Evaluador');
        })
        ->with(['evaluadorAn.area', 'roles'])
        ->find($id);

        if (!$usuario) {
            return null;
        }

        return [
            'id_usuario' => $usuario->id_usuario,
            'nombre' => $usuario->nombre,
            'apellido' => $usuario->apellido,
            'ci' => $usuario->ci,
            'email' => $usuario->email,
            'telefono' => $usuario->telefono,
            'areas_asignadas' => $usuario->evaluadorAn->map(function ($ra) {
                return [
                    'id_area' => $ra->area->id_area,
                    'nombre_area' => $ra->area->nombre
                ];
            }),
            'olimpiadas' => $usuario->roles->map(function ($role) {
                return [
                    'id_olimpiada' => $role->pivot->id_olimpiada,
                    'rol' => $role->nombre
                ];
            }),
            'created_at' => $usuario->created_at,
            'updated_at' => $usuario->updated_at
        ];
    }

    /**
     * Obtiene responsables por área específica.
     *
     * @param int $areaId
     * @return array
     */
    public function getEvaluadoresByArea(int $areaId): array
    {
        $evaluadores = Usuario::whereHas('evaluadorAn', function ($query) use ($areaId) {
            $query->where('id_area', $areaId);
        })
        ->whereHas('roles', function ($query) {
            $query->where('nombre', 'Evaluador');
        })
        ->with(['evaluadorAn.area', 'roles'])
        ->get();

        return $evaluadores->map(function ($usuario) {
            return [
                'id_usuario' => $usuario->id_usuario,
                'nombre' => $usuario->nombre,
                'apellido' => $usuario->apellido,
                'ci' => $usuario->ci,
                'email' => $usuario->email,
                'telefono' => $usuario->telefono,
                'areas_asignadas' => $usuario->evaluadorAn->map(function ($ra) {
                    return [
                        'id_area' => $ra->area->id_area,
                        'nombre_area' => $ra->area->nombre
                    ];
                })
            ];
        })->toArray();
    }

    /**
     * Obtiene responsables por olimpiada específica.
     *
     * @param int $olimpiadaId
     * @return array
     */
    public function getEvaluadoresByOlimpiada(int $olimpiadaId): array
    {
        $evaluadores = Usuario::whereHas('roles', function ($query) use ($olimpiadaId) {
            $query->where('nombre', 'Evaluador')
                  ->where('usuario_rol.id_olimpiada', $olimpiadaId);
        })
        ->with(['evaluadorAn.area', 'roles'])
        ->get();

        return $evaluadores->map(function ($usuario) {
            return [
                'id_usuario' => $usuario->id_usuario,
                'nombre' => $usuario->nombre,
                'apellido' => $usuario->apellido,
                'ci' => $usuario->ci,
                'email' => $usuario->email,
                'telefono' => $usuario->telefono,
                'areas_asignadas' => $usuario->evaluadorAn->map(function ($ra) {
                    return [
                        'id_area' => $ra->area->id_area,
                        'nombre_area' => $ra->area->nombre
                    ];
                })
            ];
        })->toArray();
    }

    /**
     * Actualiza un usuario existente.
     *
     * @param int $id
     * @param array $data
     * @return Usuario
     */
    public function updateUsuario(int $id, array $data): Usuario
    {
        $usuario = Usuario::findOrFail($id);

        $updateData = [];
        if (isset($data['nombre'])) $updateData['nombre'] = $data['nombre'];
        if (isset($data['apellido'])) $updateData['apellido'] = $data['apellido'];
        if (isset($data['ci'])) $updateData['ci'] = $data['ci'];
        if (isset($data['email'])) $updateData['email'] = $data['email'];
        if (isset($data['password'])) $updateData['password'] = Hash::make($data['password']);
        if (isset($data['telefono'])) $updateData['telefono'] = $data['telefono'];

        $usuario->update($updateData);
        return $usuario->fresh();
    }

    /**
     * Actualiza las relaciones del responsable con las áreas.
     *
     * @param Usuario $usuario
     * @param array $areaIds
     * @param int $olimpiadaId
     * @return void
     */
    public function updateEvaluadorAreaRelations(Usuario $usuario, array $areaIds, int $olimpiadaId): void
    {
        // Eliminar relaciones existentes
        EvaluadorAn::where('id_usuario', $usuario->id_usuario)->delete();

        // Crear nuevas relaciones
        $this->createEvaluadorAreaRelations($usuario, $areaIds, $olimpiadaId);
    }

    /**
     * Elimina un responsable y todas sus relaciones.
     *
     * @param int $id
     * @return bool
     */
    public function deleteEvaluador(int $id): bool
    {
        $usuario = Usuario::find($id);
        
        if (!$usuario) {
            return false;
        }

        // Eliminar relaciones con áreas
        EvaluadorAn::where('id_usuario', $id)->delete();

        // Eliminar relaciones con roles
        $usuario->roles()->detach();

        // Eliminar usuario
        return $usuario->delete();
    }

    /**
     * Busca un usuario por su CI.
     *
     * @param string $ci
     * @return Usuario|null
     */
    public function findUsuarioByCi(string $ci): ?Usuario
    {
        return Usuario::where('ci', $ci)->first();
    }


     /**
     * Encuentra las gestiones (olimpiadas) en las que un responsable ha trabajado, buscado por CI.
     *
     * @param string $ci
     * @return array
     */
    public function findGestionesByCi(string $ci): array
    {
        $usuario = Usuario::where('ci', $ci)
            ->whereHas('roles', function ($query) {
                $query->where('nombre', 'Evaluador');
            })
            ->with('roles')
            ->first();

        if (!$usuario) {
            return [];
        }

        $olimpiadaIds = $usuario->roles->pluck('pivot.id_olimpiada')->unique()->values();

        $olimpiadas = \App\Model\Olimpiada::whereIn('id_olimpiada', $olimpiadaIds)
            ->get(['id_olimpiada', 'gestion']);

        return $olimpiadas->map(function ($olimpiada) {
            return [
                'Id_olimpiada' => $olimpiada->id_olimpiada,
                'gestion' => $olimpiada->gestion,
            ];
        })->toArray();
    }

    /**
      * Encuentra las áreas asignadas a un evaluador por su CI y una gestión específica.
     *
     * @param string $ci
     * @param string $gestion
     * @return array
     */
    public function findAreasByCiAndGestion(string $ci, string $gestion): array
    {
        $usuario = Usuario::where('ci', $ci)
            ->whereHas('roles', function ($query) use ($gestion) {
                $query->where('nombre', 'Evaluador');
                $query->whereIn('usuario_rol.id_olimpiada', function ($subquery) use ($gestion) {
                    $subquery->select('id_olimpiada')->from('olimpiada')->where('gestion', $gestion);
                });
            })
            ->with(['evaluadorAn.areaOlimpiada.olimpiada', 'evaluadorAn.area'])
            ->first();

        if (!$usuario) {
            return [];
        }

        // Filtrar las áreas para que coincidan solo con la gestión solicitada
        $areasDeLaGestion = $usuario->evaluadorAn->filter(function ($evaluadorAn) use ($gestion) {
            return $evaluadorAn->areaOlimpiada && $evaluadorAn->areaOlimpiada->olimpiada->gestion == $gestion;
        });

        // Formatear la salida como se solicitó
        return $areasDeLaGestion->map(function ($evaluadorAn) {
            return [
                'id_evaluador_area' => $evaluadorAn->id_evaluadorAN,
                'Area' => [
                    'Id_area' => $evaluadorAn->area->id_area,
                    'Nombre' => $evaluadorAn->area->nombre,
                ]
            ];
        })->values()->toArray();
    }

    /**
     * Formatea los datos de un usuario responsable.
     *
     * @param Usuario $usuario
     * @param bool $includeOlimpiadas
     * @return array
     */
    private function formatEvaludorData(Usuario $usuario, bool $includeOlimpiadas = true): array
    {
        $data = [
            'id_usuario' => $usuario->id_usuario,
            'nombre' => $usuario->nombre,
            'apellido' => $usuario->apellido,
            'ci' => $usuario->ci,
            'email' => $usuario->email,
            'telefono' => $usuario->telefono,
            'areas_asignadas' => $usuario->evaluadorAn->map(function ($ra) {
                return $ra->area ? ['id_area' => $ra->area->id_area, 'nombre_area' => $ra->area->nombre] : null;
            })->filter()->values(),
            'created_at' => $usuario->created_at,
            'updated_at' => $usuario->updated_at,
        ];

        if ($includeOlimpiadas) {
            $data['olimpiadas'] = $usuario->roles->map(function ($role) {
                return ['id_olimpiada' => $role->pivot->id_olimpiada, 'rol' => $role->nombre];
            });
        }

        return $data;
    }

    /**
     * Encuentra las áreas y niveles asignados a un evaluador por su ID.
     *
     * @param int $evaluadorId
     * @return array
     */
    public function findAreasNivelesByEvaluadorId(int $evaluadorId): array
    {
        $gestionActual = date('Y');

        $usuario = Usuario::where('id_usuario', $evaluadorId)
            ->whereHas('roles', function ($query) {
                $query->where('nombre', 'Evaluador');
            })
            ->with([
                'evaluadorAn' => function ($query) use ($gestionActual) {
                    $query->whereHas('areaNivel.olimpiada', function ($subQuery) use ($gestionActual) {
                        $subQuery->where('gestion', $gestionActual);
                    });
                },
                'evaluadorAn.areaNivel.area', 'evaluadorAn.areaNivel.nivel'
            ])
            ->first();

        if (!$usuario) {
            return [];
        }

        $asignaciones = $usuario->evaluadorAn;

        $areasAgrupadas = $asignaciones->groupBy('areaNivel.area.nombre')->map(function ($asignacionesPorArea, $nombreArea) {
            $niveles = $asignacionesPorArea->map(function ($asignacion) {
                return [
                    'id_nivel' => $asignacion->areaNivel->nivel->id_nivel,
                    'nombre' => $asignacion->areaNivel->nivel->nombre,
                ];
            })->unique('id_nivel')->values();

            return [
                'id_area' => $asignacionesPorArea->first()->areaNivel->area->id_area,
                'nombre_area' => $nombreArea,
                'niveles' => $niveles,
            ];
        });

        $evaluadorData = [
            'id_usuario' => $usuario->id_usuario,
            'nombre_completo' => $usuario->nombre . ' ' . $usuario->apellido,
        ];
        if ($asignaciones->isNotEmpty()) {
            $evaluadorData['id_evaluador'] = $asignaciones->first()->id_evaluadorAN;
        }

        return [
            'evaluador' => $evaluadorData,
            'areas' => $areasAgrupadas->values()->all(),
        ];
    }
}
