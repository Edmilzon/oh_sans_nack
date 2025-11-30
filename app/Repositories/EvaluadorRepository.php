<?php

namespace App\Repositories;

use App\Model\Usuario;
use App\Model\Persona;
use App\Model\EvaluadorAn;
use App\Model\Rol;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class EvaluadorRepository
{
    /**
     * Crea un nuevo usuario evaluador (Persona + Usuario).
     *
     * @param array $data
     * @return Usuario
     */
    public function createUsuario(array $data): Usuario
    {
        return DB::transaction(function () use ($data) {
            // 1. Crear Persona
            $persona = Persona::create([
                'nombre_pers'   => $data['nombre'],
                'apellido_pers' => $data['apellido'],
                'ci_pers'       => $data['ci'],
                'email_pers'    => $data['email'], // Email de contacto
                'telefono_pers' => $data['telefono'] ?? null,
            ]);

            // 2. Crear Usuario asociado
            $usuario = Usuario::create([
                'id_persona'       => $persona->id_persona,
                'email_usuario'    => $data['email'], // Email de login (usualmente el mismo)
                'password_usuario' => Hash::make($data['password']),
            ]);

            return $usuario;
        });
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
        // BD V8: nombre_rol
        $rolEvaluador = Rol::where('nombre_rol', 'Evaluador')->first();

        if (!$rolEvaluador) {
            throw new \Exception('El rol "Evaluador" no existe en el sistema');
        }

        $usuario->asignarRol('Evaluador', $olimpiadaId);
    }

    /**
     * Crea las relaciones entre el evaluador y las áreas (AreaNivel).
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
                'id_usuario'    => $usuario->id_usuario,
                'id_area_nivel' => $areaNivelId,
                'estado_eva_an' => true, // Por defecto activo
            ]);

            // Cargar relaciones anidadas para el retorno (AreaNivel -> AreaOlimpiada -> Area)
            $evaluadorArea->load('areaNivel.areaOlimpiada.area', 'areaNivel.nivel');
            $evaluadorAreas[] = $evaluadorArea;
        }

        return $evaluadorAreas;
    }

    /**
     * Añade nuevas relaciones entre el evaluador y las áreas, evitando duplicados.
     */
    public function addEvaluadorAreaRelations(Usuario $usuario, array $areaNivelIds, int $olimpiadaId): void
    {
        foreach ($areaNivelIds as $areaNivelId) {
            EvaluadorAn::firstOrCreate(
                [
                    'id_usuario'    => $usuario->id_usuario,
                    'id_area_nivel' => $areaNivelId,
                ],
                ['estado_eva_an' => true]
            );
        }

        if (!$usuario->tieneRol('Evaluador', $olimpiadaId)) {
            $this->assignEvaluadorRole($usuario, $olimpiadaId);
        }
    }

    /**
     * Alias para compatibilidad (mismo funcionamiento que addEvaluadorAreaRelations)
     */
    public function addEvaluadorAreaNivelRelations(Usuario $usuario, array $areaNivelIds, int $olimpiadaId): void
    {
        $this->addEvaluadorAreaRelations($usuario, $areaNivelIds, $olimpiadaId);
    }

    /**
     * Obtiene todos los evaluadores con sus áreas asignadas.
     *
     * @return array
     */
    public function getAllEvaluadoresWithAreas(): array
    {
        $evaluadores = Usuario::whereHas('roles', function ($query) {
            $query->where('nombre_rol', 'Evaluador');
        })
        ->with(['persona', 'evaluadorAn.areaNivel.areaOlimpiada.area', 'roles'])
        ->get();

        return $evaluadores->map(function ($usuario) {
            return $this->formatEvaludorData($usuario);
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
            $query->where('nombre_rol', 'Evaluador');
        })
        ->with(['persona', 'evaluadorAn.areaNivel.areaOlimpiada.area', 'roles'])
        ->find($id);

        if (!$usuario) {
            return null;
        }

        return $this->formatEvaludorData($usuario);
    }

    /**
     * Obtiene evaluadores por área específica (ID de Area, no AreaNivel).
     * Navega: EvaluadorAn -> AreaNivel -> AreaOlimpiada -> Area
     *
     * @param int $areaId
     * @return array
     */
    public function getEvaluadoresByArea(int $areaId): array
    {
        $evaluadores = Usuario::whereHas('evaluadorAn.areaNivel.areaOlimpiada', function ($query) use ($areaId) {
            $query->where('id_area', $areaId);
        })
        ->whereHas('roles', function ($query) {
            $query->where('nombre_rol', 'Evaluador');
        })
        ->with(['persona', 'evaluadorAn.areaNivel.areaOlimpiada.area', 'roles'])
        ->get();

        return $evaluadores->map(function ($usuario) {
            return $this->formatEvaludorData($usuario);
        })->toArray();
    }

    /**
     * Obtiene evaluadores por olimpiada específica.
     *
     * @param int $olimpiadaId
     * @return array
     */
    public function getEvaluadoresByOlimpiada(int $olimpiadaId): array
    {
        $evaluadores = Usuario::whereHas('roles', function ($query) use ($olimpiadaId) {
            $query->where('nombre_rol', 'Evaluador')
                  ->where('usuario_rol.id_olimpiada', $olimpiadaId);
        })
        ->with(['persona', 'evaluadorAn.areaNivel.areaOlimpiada.area', 'roles'])
        ->get();

        return $evaluadores->map(function ($usuario) {
            return $this->formatEvaludorData($usuario);
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
        return DB::transaction(function () use ($id, $data) {
            $usuario = Usuario::with('persona')->findOrFail($id);
            $persona = $usuario->persona;

            // Actualizar Datos Personales
            $personaData = [];
            if (isset($data['nombre'])) $personaData['nombre_pers'] = $data['nombre'];
            if (isset($data['apellido'])) $personaData['apellido_pers'] = $data['apellido'];
            if (isset($data['ci'])) $personaData['ci_pers'] = $data['ci'];
            if (isset($data['email'])) $personaData['email_pers'] = $data['email'];
            if (isset($data['telefono'])) $personaData['telefono_pers'] = $data['telefono'];

            if (!empty($personaData)) {
                $persona->update($personaData);
            }

            // Actualizar Datos de Usuario (Auth)
            $usuarioData = [];
            if (isset($data['email'])) $usuarioData['email_usuario'] = $data['email'];
            if (isset($data['password'])) $usuarioData['password_usuario'] = Hash::make($data['password']);

            if (!empty($usuarioData)) {
                $usuario->update($usuarioData);
            }

            return $usuario->fresh(['persona']);
        });
    }

    /**
     * Actualiza las relaciones del evaluador con las áreas (Reemplazo total).
     *
     * @param Usuario $usuario
     * @param array $areaNivelIds
     * @param int $olimpiadaId
     * @return void
     */
    public function updateEvaluadorAreaRelations(Usuario $usuario, array $areaNivelIds, int $olimpiadaId): void
    {
        // Eliminar relaciones existentes
        EvaluadorAn::where('id_usuario', $usuario->id_usuario)->delete();

        // Crear nuevas relaciones
        $this->createEvaluadorAreaRelations($usuario, $areaNivelIds, $olimpiadaId);
    }

    /**
     * Elimina un evaluador y sus relaciones.
     */
    public function deleteEvaluador(int $id): bool
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return false;
        }

        // Eloquent se encarga de borrar en cascada si está configurado en BD,
        // pero para seguridad borramos relaciones manuales.
        EvaluadorAn::where('id_usuario', $id)->delete();
        $usuario->roles()->detach();

        // Opcional: Borrar Persona si no se usa en otro lado (lógica de negocio)
        // $usuario->persona()->delete();

        return $usuario->delete();
    }

    /**
     * Busca un usuario por su CI (en tabla Persona).
     *
     * @param string $ci
     * @return Usuario|null
     */
    public function findUsuarioByCi(string $ci): ?Usuario
    {
        return Usuario::whereHas('persona', function($q) use ($ci) {
            $q->where('ci_pers', $ci);
        })->first();
    }

     /**
     * Encuentra las gestiones (olimpiadas) en las que un evaluador ha trabajado.
     */
    public function findGestionesByCi(string $ci): array
    {
        $usuario = $this->findUsuarioByCi($ci);

        if (!$usuario) {
            return [];
        }

        // Verificar que tenga rol evaluador
        if (!$usuario->tieneRol('Evaluador')) {
            return [];
        }

        $olimpiadaIds = $usuario->roles()
            ->where('nombre_rol', 'Evaluador')
            ->pluck('usuario_rol.id_olimpiada')
            ->unique()
            ->values();

        $olimpiadas = \App\Model\Olimpiada::whereIn('id_olimpiada', $olimpiadaIds)
            ->get(['id_olimpiada', 'gestion_olimp']);

        return $olimpiadas->map(function ($olimpiada) {
            return [
                'Id_olimpiada' => $olimpiada->id_olimpiada,
                'gestion' => $olimpiada->gestion_olimp,
            ];
        })->toArray();
    }

    /**
     * Encuentra las áreas asignadas a un evaluador por su CI y una gestión específica.
     */
    public function findAreasByCiAndGestion(string $ci, string $gestion): array
    {
        $usuario = $this->findUsuarioByCi($ci);

        if (!$usuario) {
            return [];
        }

        // Cargar asignaciones filtrando por la gestión en la relación anidada
        $usuario->load(['evaluadorAn' => function($q) use ($gestion) {
            $q->whereHas('areaNivel.areaOlimpiada.olimpiada', function($sub) use ($gestion) {
                $sub->where('gestion_olimp', $gestion);
            });
            $q->with('areaNivel.areaOlimpiada.area');
        }]);

        return $usuario->evaluadorAn->map(function ($evaluadorAn) {
            $area = $evaluadorAn->areaNivel->areaOlimpiada->area;
            return [
                'id_evaluador_an' => $evaluadorAn->id_evaluador_an,
                'Area' => [
                    'Id_area' => $area->id_area,
                    'Nombre' => $area->nombre_area,
                ]
            ];
        })->values()->toArray();
    }

    /**
     * Encuentra las áreas y niveles asignados a un evaluador por su ID.
     */
    public function findAreasNivelesByEvaluadorId(int $evaluadorId): array
    {
        $gestionActual = date('Y');

        $usuario = Usuario::with([
            'persona',
            'evaluadorAn' => function ($query) use ($gestionActual) {
                $query->whereHas('areaNivel.areaOlimpiada.olimpiada', function ($subQuery) use ($gestionActual) {
                    $subQuery->where('gestion_olimp', $gestionActual);
                });
            },
            'evaluadorAn.areaNivel.areaOlimpiada.area',
            'evaluadorAn.areaNivel.nivel'
        ])
        ->where('id_usuario', $evaluadorId)
        ->first();

        if (!$usuario) {
            return [];
        }

        $asignaciones = $usuario->evaluadorAn;

        // Agrupar por nombre de área
        $areasAgrupadas = $asignaciones->groupBy(function($item) {
            return $item->areaNivel->areaOlimpiada->area->nombre_area;
        })->map(function ($asignacionesPorArea, $nombreArea) {

            $niveles = $asignacionesPorArea->map(function ($asignacion) {
                return [
                    'id_nivel' => $asignacion->areaNivel->nivel->id_nivel,
                    'nombre' => $asignacion->areaNivel->nivel->nombre_nivel,
                ];
            })->unique('id_nivel')->values();

            return [
                'id_area' => $asignacionesPorArea->first()->areaNivel->areaOlimpiada->area->id_area,
                'nombre_area' => $nombreArea,
                'niveles' => $niveles,
            ];
        });

        $evaluadorData = [
            'id_usuario' => $usuario->id_usuario,
            'nombre_completo' => $usuario->persona->nombre_pers . ' ' . $usuario->persona->apellido_pers,
        ];

        if ($asignaciones->isNotEmpty()) {
            $evaluadorData['id_evaluador'] = $asignaciones->first()->id_evaluador_an;
        }

        return [
            'evaluador' => $evaluadorData,
            'areas' => $areasAgrupadas->values()->all(),
        ];
    }

    /**
     * Helper privado para formatear la respuesta JSON del Evaluador.
     * Mapea los campos de la BD V8 a la estructura que espera el Frontend.
     */
    private function formatEvaludorData(Usuario $usuario): array
    {
        // Asegurar que las relaciones estén cargadas
        $usuario->loadMissing('persona', 'evaluadorAn.areaNivel.areaOlimpiada.area', 'evaluadorAn.areaNivel.nivel', 'roles');

        $areasAsignadas = $usuario->evaluadorAn->map(function ($ea) {
            $area = $ea->areaNivel->areaOlimpiada->area ?? null;
            $nivel = $ea->areaNivel->nivel ?? null;

            if (!$area) return null;

            return [
                'id_area' => $area->id_area,
                'nombre_area' => $area->nombre_area,
                'nivel' => $nivel ? $nivel->nombre_nivel : null // Info extra útil
            ];
        })->filter()->values(); // filter elimina nulos

        // Obtener olimpiadas desde roles
        $olimpiadas = $usuario->roles->map(function ($role) {
            return [
                'id_olimpiada' => $role->pivot->id_olimpiada,
                'rol' => $role->nombre_rol
            ];
        });

        return [
            'id_usuario' => $usuario->id_usuario,
            // Mapeo Persona -> Frontend keys
            'nombre'    => $usuario->persona->nombre_pers,
            'apellido'  => $usuario->persona->apellido_pers,
            'ci'        => $usuario->persona->ci_pers,
            'telefono'  => $usuario->persona->telefono_pers,
            'email'     => $usuario->email_usuario,

            'areas_asignadas' => $areasAsignadas,
            'olimpiadas'      => $olimpiadas,

            'created_at' => $usuario->created_at,
            'updated_at' => $usuario->updated_at
        ];
    }
}
