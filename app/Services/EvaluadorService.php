<?php

namespace App\Services;

use App\Repositories\EvaluadorRepository;
use App\Model\Usuario;
use App\Model\ResponsableArea;
use App\Model\Area;
use App\Mail\UserCredentialsMail;
use Illuminate\Support\Facades\Mail;
use App\Model\EvaluadorAn;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Exception;

class EvaluadorService
{
    protected $evaluadorRepository;

    public function __construct(EvaluadorRepository $evaluadorRepository)
    {
        $this->evaluadorRepository = $evaluadorRepository;
    }

    /**
     * Crea un nuevo evaluador (Persona + Usuario).
     *
     * @param array $data Contiene 'nombre', 'apellido', 'ci', 'email', 'password' (texto plano del front), etc.
     * @return array
     * @throws \Exception
     */
    public function createEvaluador(array $data): array
    {
        return DB::transaction(function () use ($data) {

            // 1. **COMPATIBILIDAD CON FRONTEND:** Usamos la contraseña enviada por el front para el correo
            // El Repositorio se encarga de HASHear antes de guardar.
            $plainPassword = $data['password'];

            // 2. Crear el usuario (Repositorio maneja Persona + Usuario y hasheo)
            $usuario = $this->evaluadorRepository->createUsuario($data);

            // 3. Asignar rol de "Evaluador"
            $this->evaluadorRepository->assignEvaluadorRole($usuario, $data['id_olimpiada']);

            // 4. Crear relaciones con las áreas (AreaNivel)
            $evaluadorAreas = $this->evaluadorRepository->createEvaluadorAreaRelations(
                $usuario,
                $data['area_nivel_ids'],
                $data['id_olimpiada']
            );

            // 5. Enviar correo con las credenciales
            Mail::to($usuario->email_usuario)->send(new UserCredentialsMail( // Columna corregida
                $usuario->persona->nombre_pers, // Acceso corregido
                $usuario->email_usuario,       // Columna corregida
                $plainPassword,                // Contraseña en texto plano (enviada por el front)
                'Evaluador'
            ));

            // 6. Retornar datos mapeados
            return $this->getEvaluadorData($usuario->fresh(['persona']));
        });
    }

    /**
     * Obtiene todos los evaluadores.
     * El Repositorio ya retorna la data mapeada
     * @return array
     */
    public function getAllEvaluadores(): array
    {
        return $this->evaluadorRepository->getAllEvaluadoresWithAreas();
    }

    /**
     * Obtiene un evaluador específico por ID.
     * @param int $id
     * @return array|null
     */
    public function getEvaluadorById(int $id): ?array
    {
        return $this->evaluadorRepository->getEvaluadorByIdWithAreas($id);
    }

    /**
     * Obtiene evaluadores por área específica.
     * @param int $areaId
     * @return array
     */
    public function getEvaluadoresByArea(int $areaId): array
    {
        return $this->evaluadorRepository->getEvaluadoresByArea($areaId);
    }

    /**
     * Obtiene evaluadores por olimpiada específica.
     * @param int $olimpiadaId
     * @return array
     */
    public function getEvaluadoresByOlimpiada(int $olimpiadaId): array
    {
        return $this->evaluadorRepository->getEvaluadoresByOlimpiada($olimpiadaId);
    }

    /**
     * Obtiene las gestiones (olimpiadas) en las que ha trabajado un evaluador.
     * @param string $ci
     * @return array
     */
    public function getGestionesByCi(string $ci): array
    {
        return $this->evaluadorRepository->findGestionesByCi($ci);
    }

    /**
     * Obtiene las áreas asignadas a un evaluador para una gestión específica.
     * @param string $ci
     * @param string $gestion
     * @return array
     */
    public function getAreasByCiAndGestion(string $ci, string $gestion): array
    {
        return $this->evaluadorRepository->findAreasByCiAndGestion($ci, $gestion);
    }

    /**
     * Actualiza un evaluador existente.
     * @param int $id
     * @param array $data
     * @return array
     */
    public function updateEvaluador(int $id, array $data): array
    {
        return DB::transaction(function () use ($id, $data) {
            $usuario = $this->evaluadorRepository->updateUsuario($id, $data);

            if (isset($data['areas']) && isset($data['id_olimpiada'])) {
                $this->evaluadorRepository->updateEvaluadorAreaRelations($usuario, $data['areas'], $data['id_olimpiada']);
            }

            return $this->getEvaluadorData($usuario->fresh(['persona']));
        });
    }

    /**
     * Actualiza un evaluador existente por su CI.
     * @param string $ci
     * @param array $data
     * @return array|null
     */
    public function updateEvaluadorByCi(string $ci, array $data): ?array
    {
        $usuario = $this->evaluadorRepository->findUsuarioByCi($ci);

        if (!$usuario) {
            return null;
        }

        return DB::transaction(function () use ($usuario, $data) {
            $usuarioActualizado = $this->evaluadorRepository->updateUsuario($usuario->id_usuario, $data);

            if (isset($data['areas']) && isset($data['id_olimpiada'])) {
                $this->evaluadorRepository->updateEvaluadorAreaRelations($usuarioActualizado, $data['areas'], $data['id_olimpiada']);
            }

            return $this->getEvaluadorData($usuarioActualizado);
        });
    }

    /**
     * Añade nuevas áreas a un evaluador existente por su CI.
     * @param string $ci
     * @param array $data
     * @return array|null
     */
    public function addAreasToEvaluadorByCi(string $ci, array $data): ?array
    {
        $usuario = $this->evaluadorRepository->findUsuarioByCi($ci);

        if (!$usuario) {
            return null;
        }

        return DB::transaction(function () use ($usuario, $data) {
            $this->evaluadorRepository->addEvaluadorAreaRelations(
                $usuario,
                $data['areas'],
                $data['id_olimpiada']
            );
            return $this->getEvaluadorData($usuario->fresh(['persona']));
        });
    }

    /**
     * Añade nuevas asignaciones de área/nivel a un evaluador existente por su CI.
     * @param string $ci
     * @param array $data
     * @return array|null
     */
    public function addAsignacionesToEvaluadorByCi(string $ci, array $data): ?array
    {
        $usuario = $this->evaluadorRepository->findUsuarioByCi($ci);

        if (!$usuario) {
            return null;
        }

        return DB::transaction(function () use ($usuario, $data) {
            $this->evaluadorRepository->addEvaluadorAreaNivelRelations(
                $usuario,
                $data['area_nivel_ids'],
                $data['id_olimpiada']
            );
            return $this->getEvaluadorData($usuario->fresh(['persona']));
        });
    }

    /**
     * Elimina un evaluador.
     * @param int $id
     * @return bool
     */
    public function deleteEvaluador(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            return $this->evaluadorRepository->deleteEvaluador($id);
        });
    }

    /**
     * Valida que las áreas existan.
     * @param array $areaIds
     * @return void
     * @throws ValidationException
     */
    public function validateAreas(array $areaIds): void
    {
        $existingAreas = Area::whereIn('id_area', $areaIds)->pluck('id_area')->toArray();
        $missingAreas = array_diff($areaIds, $existingAreas);

        if (!empty($missingAreas)) {
            throw ValidationException::withMessages([
                'areas' => ['Las siguientes áreas no existen: ' . implode(', ', $missingAreas)]
            ]);
        }
    }

    /**
     * Obtiene los datos formateados del evaluador.
     * Mapea los campos de Persona y las relaciones anidadas a la estructura simple de salida.
     *
     * @param Usuario $usuario
     * @param array|null $evaluadorAreas Se usa principalmente para la respuesta inmediata del create.
     * @return array
     */
    private function getEvaluadorData(Usuario $usuario, ?array $evaluadorAreas = null): array
    {
        // Asegurar que las relaciones estén cargadas para mapear
        $usuario->loadMissing('persona', 'evaluadorAn.areaNivel.areaOlimpiada.area', 'evaluadorAn.areaNivel.nivel');

        // Si no se pasaron las áreas explícitamente, las obtenemos del modelo cargado.
        if (!$evaluadorAreas) {
            $evaluadorAreas = $usuario->evaluadorAn;
        }

        return [
            'id_usuario' => $usuario->id_usuario,
            // Mapeo Persona -> Frontend keys
            'nombre' => $usuario->persona->nombre_pers,
            'apellido' => $usuario->persona->apellido_pers,
            'ci' => $usuario->persona->ci_pers,
            'email' => $usuario->email_usuario, // Columna corregida
            'telefono' => $usuario->persona->telefono_pers ?? null, // Columna corregida
            'rol' => 'Evaluador',

            // Mapear asignaciones (Area/Nivel)
            'asignaciones' => collect($evaluadorAreas)->map(function ($ea) {
                // Navegación corregida para obtener Area y Nivel
                $area = $ea->areaNivel->areaOlimpiada->area ?? null;
                $nivel = $ea->areaNivel->nivel ?? null;

                if (!$area || !$nivel) return null; // Filtrar relaciones rotas

                return [
                    'area' => $area->nombre_area, // Columna corregida
                    'nivel' => $nivel->nombre_nivel, // Columna corregida
                ];
            })->filter()->values()->toArray(),

            'created_at' => $usuario->created_at,
            'updated_at' => $usuario->updated_at
        ];
    }

    /**
     * Obtiene las áreas y niveles asignados a un evaluador por su ID.
     * @param int $id
     * @return array
     */
    public function getAreasNivelesByEvaluadorId(int $id): array
    {
        // El Repositorio ya retorna la data mapeada
        return $this->evaluadorRepository->findAreasNivelesByEvaluadorId($id);
    }
}
