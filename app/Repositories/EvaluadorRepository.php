<?php

namespace App\Repositories;

use App\Model\Usuario;
use App\Model\Persona;
use App\Model\Rol;
use App\Model\EvaluadorAn;
use App\Model\UsuarioRol;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use Exception;

class EvaluadorRepository
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
        ]);
    }

    public function assignEvaluadorRole(Usuario $usuario, int $idOlimpiada): void
    {
        $rol = Rol::where('nombre', 'Evaluador')->first();

        if (!$rol) {
            throw new Exception("El rol 'Evaluador' no existe en la base de datos.");
        }

        $existe = $usuario->roles()
            ->where('usuario_rol.id_rol', $rol->id_rol)
            ->wherePivot('id_olimpiada', $idOlimpiada)
            ->exists();

        if (!$existe) {
            $usuario->roles()->attach($rol->id_rol, [
                'id_olimpiada' => $idOlimpiada
            ]);
        }
    }

    public function syncEvaluadorAreas(Usuario $usuario, array $areaNivelIds): void
    {
        foreach ($areaNivelIds as $idAreaNivel) {

            EvaluadorAn::firstOrCreate([
                'id_usuario'    => $usuario->id_usuario,
                'id_area_nivel' => $idAreaNivel
            ], [
                'estado' => true
            ]);
        }
    }

    public function getById(int $id): ?array
    {

        $usuario = Usuario::with([
            'persona',
            'evaluadoresAn.areaNivel.areaOlimpiada.area',
            'evaluadoresAn.areaNivel.nivel'
        ])->find($id);

        if (!$usuario) {
            return null;
        }

        return $this->mapToLegacyJson($usuario);
    }

    public function getAllEvaluadores(): Collection
    {
        return Usuario::whereHas('roles', function (Builder $q) {
                $q->where('nombre', 'Evaluador');
            })
            ->with(['persona', 'evaluadoresAn'])
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

            'areas_asignadas' => $usuario->evaluadoresAn->map(function($ean) {

                $areaNivel      = $ean->areaNivel;
                $areaOlimpiada  = $areaNivel->areaOlimpiada ?? null;
                $nivel          = $areaNivel->nivel ?? null;

                $nombreArea     = $areaOlimpiada->area->nombre ?? 'Sin Área';
                $nombreNivel    = $nivel->nombre ?? 'Sin Nivel';

                return [
                    'id_evaluador_an'  => $ean->id_evaluador_an,
                    'id_area_olimpiada'=> $areaNivel->id_area_olimpiada ?? null,
                    'id_area_nivel'    => $ean->id_area_nivel,
                    'id_nivel'         => $areaNivel->id_nivel ?? null,
                    'area'             => $nombreArea,
                    'nivel'            => $nombreNivel,
                    'gestion'          => $areaOlimpiada->olimpiada->gestion ?? null
                ];
            })->values()->toArray()
        ];
    }
}
