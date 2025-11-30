<?php

namespace App\Repositories;

use App\Model\Usuario;
use App\Model\Persona;
use App\Model\ResponsableArea;
use App\Model\Rol;
use App\Model\Area;
use App\Model\AreaOlimpiada;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class ResponsableRepository
{
    /**
     * Crea un nuevo usuario responsable (Persona + Usuario).
     *
     * @param array $data Contiene 'nombre', 'apellido', 'ci', 'email', 'password', 'telefono'.
     * @return Usuario
     */
    public function createUsuario(array $data): Usuario
    {
        return DB::transaction(function () use ($data) {
            // 1. Crear Persona (Datos personales)
            $persona = Persona::create([
                'nombre_pers'   => $data['nombre'],
                'apellido_pers' => $data['apellido'],
                'ci_pers'       => $data['ci'],
                'email_pers'    => $data['email'],
                'telefono_pers' => $data['telefono'] ?? null,
            ]);

            // 2. Crear Usuario (Credenciales)
            $usuario = Usuario::create([
                'id_persona'       => $persona->id_persona,
                'email_usuario'    => $data['email'],
                'password_usuario' => Hash::make($data['password']),
            ]);

            return $usuario;
        });
    }

    /**
     * Asigna el rol de "Responsable Area" al usuario.
     */
    public function assignResponsableRole(Usuario $usuario, int $olimpiadaId): void
    {
        $rolResponsable = Rol::where('nombre_rol', 'Responsable Area')->first(); // Columna corregida

        if (!$rolResponsable) {
            throw new \Exception('El rol "Responsable Area" no existe en el sistema');
        }

        $usuario->asignarRol('Responsable Area', $olimpiadaId);
    }

    /**
     * Crea las relaciones entre el responsable y las áreas.
     * Carga de relaciones corregida: ResponsableArea -> AreaOlimpiada -> Area
     */
    public function createResponsableAreaRelations(Usuario $usuario, array $areaIds, int $olimpiadaId): array
    {
        $responsableAreas = [];

        foreach ($areaIds as $areaId) {
            $areaOlimpiada = AreaOlimpiada::where('id_area', $areaId)
                                         ->where('id_olimpiada', $olimpiadaId)
                                         ->first();

            if (!$areaOlimpiada) {
                throw new \Exception("La combinación del área ID {$areaId} y la olimpiada ID {$olimpiadaId} no existe.");
            }

            $responsableArea = ResponsableArea::create([
                'id_usuario' => $usuario->id_usuario,
                'id_area_olimpiada' => $areaOlimpiada->id_area_olimpiada,
            ]);

            // Carga de relación corregida
            $responsableAreas[] = $responsableArea->load('areaOlimpiada.area');
        }
        return $responsableAreas;
    }


    public function addResponsableAreaRelations(Usuario $usuario, array $areaIds, int $olimpiadaId): void
    {
        foreach ($areaIds as $areaId) {
            $areaOlimpiada = AreaOlimpiada::where('id_area', $areaId)
                                         ->where('id_olimpiada', $olimpiadaId)
                                         ->first();

            if (!$areaOlimpiada) {
                throw new \Exception("La combinación del área ID {$areaId} y la olimpiada ID {$olimpiadaId} no existe.");
            }

            ResponsableArea::firstOrCreate(
                [
                    'id_usuario' => $usuario->id_usuario,
                    'id_area_olimpiada' => $areaOlimpiada->id_area_olimpiada,
                ]
            );
        }

        if (!$usuario->tieneRol('Responsable Area', $olimpiadaId)) {
            $this->assignResponsableRole($usuario, $olimpiadaId);
        }
    }

    /**
     * Obtiene todos los responsables con sus áreas asignadas.
     */
    public function getAllResponsablesWithAreas(): array
    {
        $responsables = Usuario::whereHas('roles', function ($query) {
            $query->where('nombre_rol', 'Responsable Area'); // Columna corregida
        })
        ->with(['persona', 'responsableArea.areaOlimpiada.area', 'roles'])->get(); // Carga corregida

        return $responsables->map(fn($usuario) => $this->formatResponsableData($usuario, true))->toArray();
    }

    /**
     * Obtiene un responsable específico por ID con sus áreas.
     */
    public function getResponsableByIdWithAreas(int $id): ?array
    {
        $usuario = Usuario::whereHas('roles', function ($query) {
            $query->where('nombre_rol', 'Responsable Area'); // Columna corregida
        })
        ->with(['persona', 'responsableArea.areaOlimpiada.area', 'roles'])->find($id); // Carga corregida

        if (!$usuario) {
            return null;
        }
        return $this->formatResponsableData($usuario, true);
    }

    /**
     * Obtiene responsables por área específica.
     */
    public function getResponsablesByArea(int $areaId): array
    {
        $responsables = Usuario::whereHas('responsableArea.areaOlimpiada', function ($query) use ($areaId) {
            $query->where('id_area', $areaId); // Navegamos a AreaOlimpiada que tiene id_area
        })
        ->whereHas('roles', function ($query) {
            $query->where('nombre_rol', 'Responsable Area'); // Columna corregida
        })
        ->with(['persona', 'responsableArea.areaOlimpiada.area', 'roles']) // Carga corregida
        ->get();

        return $responsables->map(fn($usuario) => $this->formatResponsableData($usuario, false))->toArray();
    }

    /**
     * Obtiene responsables por olimpiada específica.
     */
    public function getResponsablesByOlimpiada(int $olimpiadaId): array
    {
        $responsables = Usuario::whereHas('roles', function ($query) use ($olimpiadaId) {
            $query->where('nombre_rol', 'Responsable Area') // Columna corregida
                  ->where('usuario_rol.id_olimpiada', $olimpiadaId);
        })
        ->with(['persona', 'responsableArea.areaOlimpiada.area', 'roles']) // Carga corregida
        ->get();

        return $responsables->map(fn($usuario) => $this->formatResponsableData($usuario, false))->toArray();
    }

    /**
     * Encuentra las gestiones (olimpiadas) en las que un responsable ha trabajado, buscado por CI.
     */
    public function findGestionesByCi(string $ci): array
    {
        $usuario = $this->findUsuarioByCi($ci); // Usar el método corregido findUsuarioByCi

        if (!$usuario) {
            return [];
        }

        $olimpiadaIds = $usuario->roles()
             ->where('nombre_rol', 'Responsable Area') // Columna corregida
             ->pluck('usuario_rol.id_olimpiada')
             ->unique()
             ->values();

        $olimpiadas = \App\Model\Olimpiada::whereIn('id_olimpiada', $olimpiadaIds)
            ->get(['id_olimpiada', 'gestion_olimp']); // Columna corregida

        return $olimpiadas->map(function ($olimpiada) {
            return [
                'Id_olimpiada' => $olimpiada->id_olimpiada,
                'gestion' => $olimpiada->gestion_olimp, // Columna corregida
            ];
        })->toArray();
    }

    /**
     * Encuentra las áreas asignadas a un responsable por su CI y una gestión específica.
     */
    public function findAreasByCiAndGestion(string $ci, string $gestion): array
    {
        $usuario = $this->findUsuarioByCi($ci); // Usar el método corregido findUsuarioByCi

        if (!$usuario) {
            return [];
        }

        // Cargar asignaciones filtrando por la gestión en la relación anidada
        $usuario->load(['responsableArea' => function ($q) use ($gestion) {
            $q->whereHas('areaOlimpiada.olimpiada', function ($sub) use ($gestion) {
                $sub->where('gestion_olimp', $gestion); // Columna corregida
            });
            $q->with('areaOlimpiada.area'); // Carga la información del área
        }]);

        // Filtrar las áreas para que coincidan solo con la gestión solicitada
        $areasDeLaGestion = $usuario->responsableArea->filter(function ($responsableArea) use ($gestion) {
            // Columna corregida
            return $responsableArea->areaOlimpiada && $responsableArea->areaOlimpiada->olimpiada->gestion_olimp == $gestion;
        });

        return $areasDeLaGestion->map(function ($responsableArea) {
            $area = $responsableArea->areaOlimpiada->area; // Acceder al modelo Area

            return [
                'id_responsable_area' => $responsableArea->id_responsable_area, // Columna corregida
                'Area' => [
                    'Id_area' => $area->id_area,
                    'Nombre' => $area->nombre_area, // Columna corregida
                ]
            ];
        })->values()->toArray();
    }

    /**
     * Actualiza un usuario existente (Persona + Usuario).
     */
    public function updateUsuario(int $id, array $data): Usuario
    {
        return DB::transaction(function () use ($id, $data) {
            $usuario = Usuario::with('persona')->findOrFail($id);
            $persona = $usuario->persona;

            // 1. Actualizar Datos Personales (Persona)
            $personaData = [];
            if (isset($data['nombre'])) $personaData['nombre_pers'] = $data['nombre'];
            if (isset($data['apellido'])) $personaData['apellido_pers'] = $data['apellido'];
            if (isset($data['ci'])) $personaData['ci_pers'] = $data['ci'];
            if (isset($data['email'])) $personaData['email_pers'] = $data['email'];
            if (isset($data['telefono'])) $personaData['telefono_pers'] = $data['telefono'];

            if (!empty($personaData)) {
                $persona->update($personaData);
            }

            // 2. Actualizar Datos de Usuario (Auth)
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
     * Actualiza las relaciones del responsable con las áreas.
     */
    public function updateResponsableAreaRelations(Usuario $usuario, array $areaIds, int $olimpiadaId): void
    {
        ResponsableArea::where('id_usuario', $usuario->id_usuario)->delete();
        $this->createResponsableAreaRelations($usuario, $areaIds, $olimpiadaId);
    }

    /**
     * Elimina un responsable y todas sus relaciones.
     */
    public function deleteResponsable(int $id): bool
    {
        $usuario = Usuario::find($id);

        if (!$usuario) {
            return false;
        }

        ResponsableArea::where('id_usuario', $id)->delete();
        $usuario->roles()->detach();

        // Opcional: Borrar Persona si no se usa en otro lado
        // $usuario->persona()->delete();

        return $usuario->delete();
    }

    /**
     * Formatea los datos de un usuario responsable.
     */
    private function formatResponsableData(Usuario $usuario, bool $includeOlimpiadas = true): array
    {
        // Asegurar que las relaciones estén cargadas
        $usuario->loadMissing('persona', 'responsableArea.areaOlimpiada.area', 'roles');

        $areasAsignadas = $usuario->responsableArea->map(function ($ra) {
            $area = $ra->areaOlimpiada->area ?? null;
            if (!$area) return null;

            return [
                'id_area' => $area->id_area,
                'nombre_area' => $area->nombre_area // Columna corregida
            ];
        })->filter()->values();

        $data = [
            'id_usuario' => $usuario->id_usuario,
            // Mapeo Persona -> Frontend keys
            'nombre' => $usuario->persona->nombre_pers,
            'apellido' => $usuario->persona->apellido_pers,
            'ci' => $usuario->persona->ci_pers,
            'email' => $usuario->email_usuario,
            'telefono' => $usuario->persona->telefono_pers ?? null,

            'areas_asignadas' => $areasAsignadas,
            'created_at' => $usuario->created_at,
            'updated_at' => $usuario->updated_at,
        ];

        if ($includeOlimpiadas) {
            $data['olimpiadas'] = $usuario->roles->map(function ($role) {
                return [
                    'id_olimpiada' => $role->pivot->id_olimpiada,
                    'rol' => $role->nombre_rol // Columna corregida
                ];
            });
        }

        return $data;
    }

    /**
     * Obtiene las áreas que ya tienen un responsable asignado en la gestión actual.
     */
    public function getAreasOcupadasPorGestion(string $gestion)
    {
        return Area::whereHas('areaOlimpiadas', function ($query) use ($gestion) {
            $query->whereHas('olimpiada', function ($q) use ($gestion) {
                $q->where('gestion_olimp', $gestion); // Columna corregida
            });
            $query->whereHas('responsableArea'); // Filtra aquellas AreaOlimpiadas que ya tienen ResponsableArea
        })
        ->select('id_area', 'nombre_area as nombre') // Columna corregida + Alias
        ->distinct()
        ->get();
    }

    /**
     * Busca un usuario por su CI (en tabla Persona).
     */
    public function findUsuarioByCi(string $ci): ?Usuario
    {
        // Búsqueda a través de la relación persona y columna ci_pers
        return Usuario::whereHas('persona', function(Builder $q) use ($ci) {
            $q->where('ci_pers', $ci);
        })->with('persona')->first();
    }
}
