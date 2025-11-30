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

class EvaluadorService
{
    protected $evaluadorRepository;

    public function __construct(EvaluadorRepository $evaluadorRepository)
    {
        $this->evaluadorRepository = $evaluadorRepository;
    }

    /**
     * Crea un nuevo responsable de área.
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function createEvaluador(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // Guardar la contraseña en texto plano para el correo
            $plainPassword = $data['password'];

            // Crear el usuario
            $usuario = $this->evaluadorRepository->createUsuario($data);

            // Asignar rol de "Evaluador"
            $this->evaluadorRepository->assignEvaluadorRole($usuario, $data['id_olimpiada']);

            // Crear relaciones con las áreas
            $evaluadorAreas = $this->evaluadorRepository->createEvaluadorAreaRelations(
                $usuario,
                $data['area_nivel_ids'],
                $data['id_olimpiada']
            );

            // Enviar correo con las credenciales
            Mail::to($usuario->email)->send(new UserCredentialsMail(
                $usuario->nombre,
                $usuario->email,
                $plainPassword,
                'Evaluador'
            ));

            // Obtener información completa del evaluador creado
            return $this->getEvaluadorData($usuario, $evaluadorAreas);
        });
    }

    /**
     * Obtiene todos los evaluadores.
     *
     * @return array
     */
    public function getAllEvaluadores(): array
    {
        return $this->evaluadorRepository->getAllEvaluadoresWithAreas();
    }

    /**
     * Obtiene un evaluador específico por ID.
     *
     * @param int $id
     * @return array|null
     */
    public function getEvaluadorById(int $id): ?array
    {
        return $this->evaluadorRepository->getEvaluadorByIdWithAreas($id);
    }

    /**
     * Obtiene responsables por área específica.
     *
     * @param int $areaId
     * @return array
     */
    public function getEvaluadoresByArea(int $areaId): array
    {
        return $this->evaluadorRepository->getEvaluadoresByArea($areaId);
    }

    /**
     * Obtiene responsables por olimpiada específica.
     *
     * @param int $olimpiadaId
     * @return array
     */
    public function getEvaluadoresByOlimpiada(int $olimpiadaId): array
    {
        return $this->evaluadorRepository->getEvaluadoresByOlimpiada($olimpiadaId);
    }

    /**
     * Obtiene las gestiones (olimpiadas) en las que ha trabajado un evaluador.
     *
     * @param string $ci
     * @return array
     */
    public function getGestionesByCi(string $ci): array
    {
        return $this->evaluadorRepository->findGestionesByCi($ci);
    }

    /**
     * Obtiene las áreas asignadas a un evaluador para una gestión específica.
     *
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
     *
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

            return $this->getEvaluadorData($usuario);
        });
    }

    /**
     * Actualiza un evaluador existente por su CI.
     *
     * @param string $ci
     * @param array $data
     * @return array|null
     */
    public function updateEvaluadorByCi(string $ci, array $data): ?array
    {
        $usuario = $this->evaluadorRepository->findUsuarioByCi($ci);

        if (!$usuario) {
            return null; // O lanzar una excepción si se prefiere
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
     *
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
            return $this->getEvaluadorData($usuario->fresh());
        });
    }

    /**
     * Añade nuevas asignaciones de área/nivel a un evaluador existente por su CI.
     *
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
            return $this->getEvaluadorData($usuario->fresh());
        });
    }

    /**
     * Elimina un evaluador.
     *
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
     *
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
     * Obtiene los datos formateados del responsable.
     *
     * @param Usuario $usuario
     * @param array|null $responsableAreas
     * @return array
     */
    private function getEvaluadorData(Usuario $usuario, ?array $evaluadorAreas = null): array
    {
        $usuario->loadMissing('evaluadorAn.areaNivel.area', 'evaluadorAn.areaNivel.nivel');
        if (!$evaluadorAreas) {
            $evaluadorAreas = $usuario->evaluadorAn;
        }

        return [
            'id_usuario' => $usuario->id_usuario,
            'nombre' => $usuario->nombre,
            'apellido' => $usuario->apellido,
            'ci' => $usuario->ci,
            'email' => $usuario->email,
            'telefono' => $usuario->telefono ?? null,
            'rol' => 'Evaluador',
            'asignaciones' => collect($evaluadorAreas)->map(function ($ea) {
                return [
                    'area' => $ea->areaNivel->area->nombre ?? null,
                    'nivel' => $ea->areaNivel->nivel->nombre ?? null,
                ];
            })->filter(function ($value) {
                return !is_null($value['area']) && !is_null($value['nivel']);
            })->values()
            ->toArray(),
            'created_at' => $usuario->created_at,
            'updated_at' => $usuario->updated_at
        ];
    }

    /**
     * Obtiene las áreas y niveles asignados a un evaluador por su ID.
     *
     * @param int $id
     * @return array
     */
    public function getAreasNivelesByEvaluadorId(int $id): array
    {
        return $this->evaluadorRepository->findAreasNivelesByEvaluadorId($id);
    }
}
