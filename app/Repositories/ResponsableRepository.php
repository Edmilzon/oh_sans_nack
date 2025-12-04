<?php

namespace App\Repositories;

use App\Model\Usuario;
use App\Model\Persona;
use App\Model\Rol;
use App\Model\ResponsableArea;
use App\Model\AreaOlimpiada;
use App\Model\Area;
use App\Model\Olimpiada;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Exception;

class ResponsableRepository
{

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

    public function createUsuario(Persona $persona, array $data): Usuario
    {
        return Usuario::create([
            'id_persona' => $persona->id_persona,
            'email'      => $data['email'],
            'password'   => Hash::make($data['password']),
            'estado'     => true,
        ]);
    }

    public function updateResponsable(Usuario $usuario, array $data): Usuario
    {
        $usuario->persona->update([
            'nombre'   => $data['nombre'],
            'apellido' => $data['apellido'],
            'telefono' => $data['telefono'] ?? $usuario->persona->telefono,
        ]);

        $updateData = [];
        if (isset($data['email'])) $updateData['email'] = $data['email'];
        if (isset($data['password'])) $updateData['password'] = Hash::make($data['password']);

        if (!empty($updateData)) {
            $usuario->update($updateData);
        }

        return $usuario;
    }

    public function assignResponsableRole(Usuario $usuario, int $idOlimpiada): void
    {
        $rol = Rol::where('nombre', 'Responsable Area')->firstOrFail();

        $exists = $usuario->roles()
            ->where('usuario_rol.id_rol', $rol->id_rol)
            ->wherePivot('id_olimpiada', $idOlimpiada)
            ->exists();

        if (!$exists) {
            $usuario->roles()->attach($rol->id_rol, [
                'id_olimpiada' => $idOlimpiada
            ]);
        }
    }

    public function syncResponsableAreas(Usuario $usuario, array $areaIds, int $idOlimpiada): void
    {
        foreach ($areaIds as $idArea) {

            $areaOlimpiada = AreaOlimpiada::where('id_area', $idArea)
                ->where('id_olimpiada', $idOlimpiada)
                ->first();

            if ($areaOlimpiada) {

                ResponsableArea::firstOrCreate([
                    'id_usuario'        => $usuario->id_usuario,
                    'id_area_olimpiada' => $areaOlimpiada->id_area_olimpiada
                ]);
            }
        }
    }

    public function getById(int $id): ?array
    {
        $usuario = Usuario::with([
            'persona',
            'responsableAreas.areaOlimpiada.area',
            'responsableAreas.areaOlimpiada.olimpiada'
        ])->find($id);

        if (!$usuario) return null;

        return $this->mapToLegacyJson($usuario);
    }

    public function getByCi(string $ci): ?Usuario
    {
        return Usuario::whereHas('persona', fn($q) => $q->where('ci', $ci))->first();
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

    public function getGestionesByUsuario(int $idUsuario): Collection
    {
        return DB::table('usuario_rol')
            ->join('rol', 'usuario_rol.id_rol', '=', 'rol.id_rol')
            ->join('olimpiada', 'usuario_rol.id_olimpiada', '=', 'olimpiada.id_olimpiada')
            ->where('usuario_rol.id_usuario', $idUsuario)
            ->where('rol.nombre', 'Responsable Area')
            ->select('olimpiada.id_olimpiada', 'olimpiada.gestion')
            ->distinct()
            ->get();
    }

    public function getAreasByUsuarioAndGestion(int $idUsuario, string $gestion): Collection
    {
        return ResponsableArea::where('id_usuario', $idUsuario)
            ->whereHas('areaOlimpiada.olimpiada', function($q) use ($gestion) {
                $q->where('gestion', $gestion);
            })
            ->with(['areaOlimpiada.area'])
            ->get()
            ->map(function($ra) {
                return [
                    'id_responsable_area' => $ra->id_responsable_area,
                    'Area' => [
                        'Id_area' => $ra->areaOlimpiada->area->id_area,
                        'Nombre'  => $ra->areaOlimpiada->area->nombre
                    ]
                ];
            });
    }

    public function getAreasOcupadasPorGestion(int $idOlimpiada): Collection
    {
        return Area::whereHas('areaOlimpiada', function ($q) use ($idOlimpiada) {
            $q->where('id_olimpiada', $idOlimpiada)
              ->whereHas('responsableArea');
        })->get();
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

    public function getByUsuarioAndOlimpiada(int $usuarioId, int $olimpiadaId): Collection
    {
        return ResponsableArea::query()
            ->where('id_usuario', $usuarioId)
            ->whereHas('areaOlimpiada', function ($query) use ($olimpiadaId) {
                $query->where('id_olimpiada', $olimpiadaId);
            })
            ->with([
                'areaOlimpiada.area',
                'areaOlimpiada.areaNiveles.nivel'
            ])
            ->get();
    }
}
