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
    /**
     * Busca una persona por CI y la actualiza, o crea una nueva si no existe.
     * Mantiene la integridad de datos personales evitando duplicados.
     * * @param array $data Datos validados (ci, nombre, apellido, email, telefono)
     * @return Persona
     */
    public function findOrCreatePersona(array $data): Persona
    {
        // updateOrCreate verifica si existe el CI.
        // Si existe, actualiza nombre/apellido/teléfono (por si hubo correcciones).
        // Si no existe, crea el registro.
        return Persona::updateOrCreate(
            ['ci' => $data['ci']],
            [
                'nombre'   => $data['nombre'],
                'apellido' => $data['apellido'],
                'email'    => $data['email'], // Email de contacto personal
                'telefono' => $data['telefono'] ?? null,
            ]
        );
    }

    /**
     * Crea el usuario asociado a una persona.
     * * @param Persona $persona
     * @param array $data Credenciales (email, password)
     * @return Usuario
     */
    public function createUsuario(Persona $persona, array $data): Usuario
    {
        return Usuario::create([
            'id_persona' => $persona->id_persona,
            'email'      => $data['email'], // Email de Login (usuario.email)
            'password'   => Hash::make($data['password']),
            // 'estado' => true, // Asumimos default true en BD o null
        ]);
    }

    /**
     * Asigna el rol 'Evaluador' a un usuario para una gestión (Olimpiada) específica.
     * Usa la tabla pivote 'usuario_rol' con el campo extra 'id_olimpiada'.
     * * @param Usuario $usuario
     * @param int $idOlimpiada
     * @return void
     */
    public function assignEvaluadorRole(Usuario $usuario, int $idOlimpiada): void
    {
        $rol = Rol::where('nombre', 'Evaluador')->first();

        if (!$rol) {
            throw new Exception("El rol 'Evaluador' no existe en la base de datos.");
        }

        // Verificamos si ya tiene el rol en esa olimpiada para no duplicar
        $existe = $usuario->roles()
            ->where('usuario_rol.id_rol', $rol->id_rol)
            ->wherePivot('id_olimpiada', $idOlimpiada)
            ->exists();

        if (!$existe) {
            // Attach con datos del pivote
            $usuario->roles()->attach($rol->id_rol, [
                'id_olimpiada' => $idOlimpiada
            ]);
        }
    }

    /**
     * Sincroniza (agrega) las áreas y niveles que evaluará el usuario.
     * No elimina las anteriores, solo agrega las nuevas (safe add).
     * * @param Usuario $usuario
     * @param array $areaNivelIds Array de IDs de la tabla area_nivel
     * @return void
     */
    public function syncEvaluadorAreas(Usuario $usuario, array $areaNivelIds): void
    {
        foreach ($areaNivelIds as $idAreaNivel) {
            // firstOrCreate evita duplicados en la tabla evaluador_an
            EvaluadorAn::firstOrCreate([
                'id_usuario'    => $usuario->id_usuario,
                'id_area_nivel' => $idAreaNivel
            ], [
                'estado' => true
            ]);
        }
    }

    /**
     * Obtiene un evaluador por ID formateado para el Frontend (Legacy JSON).
     * * @param int $id ID del Usuario
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        // Carga ansiosa eficiente: Persona y las áreas asignadas (con sus nombres)
        $usuario = Usuario::with([
            'persona',
            'evaluadoresAn.areaNivel.areaOlimpiada.area', // Ruta para llegar al nombre del Área
            'evaluadoresAn.areaNivel.nivel'               // Ruta para llegar al nombre del Nivel
        ])->find($id);

        if (!$usuario) {
            return null;
        }

        return $this->mapToLegacyJson($usuario);
    }

    /**
     * Obtiene todos los evaluadores activos formateados.
     * (Usado por servicios internos o reportes, aunque el Controller usa paginación directa).
     * * @return Collection
     */
    public function getAllEvaluadores(): Collection
    {
        return Usuario::whereHas('roles', function (Builder $q) {
                $q->where('nombre', 'Evaluador');
            })
            ->with(['persona', 'evaluadoresAn'])
            ->get()
            ->map(fn($u) => $this->mapToLegacyJson($u));
    }

    /**
     * Helper: Transforma el modelo relacional complejo a un array plano y simple.
     * Esto "engaña" al frontend para que crea que recibe la estructura antigua.
     * * @param Usuario $usuario
     * @return array
     */
    private function mapToLegacyJson(Usuario $usuario): array
    {
        return [
            'id_usuario' => $usuario->id_usuario,
            // Aplanamos los datos de Persona
            'nombre'     => $usuario->persona->nombre ?? '',
            'apellido'   => $usuario->persona->apellido ?? '',
            'ci'         => $usuario->persona->ci ?? '',
            'telefono'   => $usuario->persona->telefono ?? '',
            'email'      => $usuario->email,
            'activo'     => (bool) $usuario->estado, // Casteo a bool para JS

            // Detalle de áreas asignadas (Simplificado)
            'areas_asignadas' => $usuario->evaluadoresAn->map(function($ean) {

                // Navegación segura para obtener nombres
                $nombreArea = $ean->areaNivel->areaOlimpiada->area->nombre ?? 'Sin Área';
                $nombreNivel = $ean->areaNivel->nivel->nombre ?? 'Sin Nivel';

                return [
                    'id_evaluador_an' => $ean->id_evaluador_an,
                    'id_area_nivel'   => $ean->id_area_nivel,
                    'area'            => $nombreArea,
                    'nivel'           => $nombreNivel,
                    'gestion'         => $ean->areaNivel->areaOlimpiada->olimpiada->gestion ?? null
                ];
            })->values()->toArray()
        ];
    }
}
