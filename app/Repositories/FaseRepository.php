<?php

namespace App\Repositories;

use App\Model\Usuario;
use App\Model\Persona;
use App\Model\Rol;
use App\Model\ResponsableArea;
use App\Model\AreaOlimpiada;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use Exception;

class ResponsableRepository
{
    /**
     * Busca o crea la Persona (Manejo inteligente de duplicados por CI).
     */
    public function findOrCreatePersona(array $data): Persona
    {
        return Persona::updateOrCreate(
            ['ci' => $data['ci']],
            [
                'nombre'   => $data['nombre'],
                'apellido' => $data['apellido'],
                'email'    => $data['email'],
                'telefono' => $data['telefono'] ?? null,
            ]
        );
    }

    /**
     * Crea el Usuario vinculado.
     */
    public function createUsuario(Persona $persona, array $data): Usuario
    {
        return Usuario::create([
            'id_persona' => $persona->id_persona,
            'email'      => $data['email'],
            'password'   => Hash::make($data['password']),
        ]);
    }

    /**
     * Asigna el rol 'Responsable Area' con el pivote de Olimpiada.
     */
    public function assignResponsableRole(Usuario $usuario, int $idOlimpiada): void
    {
        $rol = Rol::where('nombre', 'Responsable Area')->first();

        if (!$rol) {
            throw new Exception("El rol 'Responsable Area' no existe en la BD.");
        }

        // Evitar duplicados en la tabla pivote
        if (!$usuario->roles()
                ->where('usuario_rol.id_rol', $rol->id_rol)
                ->wherePivot('id_olimpiada', $idOlimpiada)
                ->exists()) {

            $usuario->roles()->attach($rol->id_rol, [
                'id_olimpiada' => $idOlimpiada
            ]);
        }
    }

    /**
     * Vincula al usuario con las Áreas seleccionadas.
     * Traduce: id_area + id_olimpiada => id_area_olimpiada => tabla responsable_area
     */
    public function syncResponsableAreas(Usuario $usuario, array $areaIds, int $idOlimpiada): void
    {
        foreach ($areaIds as $idArea) {
            // 1. Buscar el ID intermedio (AreaOlimpiada)
            $areaOlimpiada = AreaOlimpiada::where('id_area', $idArea)
                ->where('id_olimpiada', $idOlimpiada)
                ->first();

            if ($areaOlimpiada) {
                // 2. Crear la asignación si no existe
                ResponsableArea::firstOrCreate([
                    'id_usuario'        => $usuario->id_usuario,
                    'id_area_olimpiada' => $areaOlimpiada->id_area_olimpiada
                ]);
            }
        }
    }

    /**
     * Obtiene un responsable formateado para el Frontend.
     */
    public function getById(int $id): ?array
    {
        $usuario = Usuario::with([
            'persona',
            // Navegación: ResponsableArea -> AreaOlimpiada -> Area
            'responsableAreas.areaOlimpiada.area',
            'responsableAreas.areaOlimpiada.olimpiada'
        ])->find($id);

        if (!$usuario) return null;

        return $this->mapToLegacyJson($usuario);
    }

    public function getAllResponsables(): Collection
    {
        return Usuario::whereHas('roles', function (Builder $q) {
                $q->where('nombre', 'Responsable Area');
            })
            ->with(['persona', 'responsableAreas'])
            ->get()
            ->map(fn($u) => $this->mapToLegacyJson($u));
    }

    private function mapToLegacyJson(Usuario $usuario): array
    {
        return [
            'id_usuario' => $usuario->id_usuario,
            'nombre'     => $usuario->persona->nombre ?? '',
            'apellido'   => $usuario->persona->apellido ?? '',
            'ci'         => $usuario->persona->ci ?? '',
            'telefono'   => $usuario->persona->telefono ?? '',
            'email'      => $usuario->email,
            'activo'     => (bool) $usuario->estado,

            // Lista de áreas asignadas
            'areas_asignadas' => $usuario->responsableAreas->map(function($ra) {
                return [
                    'id_responsable_area' => $ra->id_responsable_area,
                    'id_area'             => $ra->areaOlimpiada->id_area ?? null,
                    'nombre_area'         => $ra->areaOlimpiada->area->nombre ?? 'Desconocido',
                    'gestion'             => $ra->areaOlimpiada->olimpiada->gestion ?? null
                ];
            })->values()->toArray()
        ];
    }
}
