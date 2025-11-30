<?php

namespace App\Services;

use App\Repositories\ResponsableRepository;
use App\Model\Usuario;
use App\Model\ResponsableArea;
use App\Model\Area;
use App\Mail\UserCredentialsMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ResponsableService
{
    protected $responsableRepository;

    public function __construct(ResponsableRepository $responsableRepository)
    {
        $this->responsableRepository = $responsableRepository;
    }

    /**
     * Crea un nuevo responsable de área.
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function createResponsable(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $plainPassword = $data['password'];

            $usuario = $this->responsableRepository->createUsuario($data);

            $this->responsableRepository->assignResponsableRole($usuario, $data['id_olimpiada']);

            $responsableAreas = $this->responsableRepository->createResponsableAreaRelations(
                $usuario, 
                $data['areas'],
                $data['id_olimpiada']
            );

            Mail::to($usuario->email)->send(new UserCredentialsMail(
                $usuario->nombre,
                $usuario->email,
                $plainPassword,
                'Responsable de Área'
            ));

            return $this->getResponsableData($usuario, $responsableAreas);
        });
    }

    /**
     * Obtiene todos los responsables de área.
     *
     * @return array
     */
    public function getAllResponsables(): array
    {
        return $this->responsableRepository->getAllResponsablesWithAreas();
    }

    /**
     * Obtiene un responsable específico por ID.
     *
     * @param int $id
     * @return array|null
     */
    public function getResponsableById(int $id): ?array
    {
        return $this->responsableRepository->getResponsableByIdWithAreas($id);
    }

    /**
     * Obtiene responsables por área específica.
     *
     * @param int $areaId
     * @return array
     */
    public function getResponsablesByArea(int $areaId): array
    {
        return $this->responsableRepository->getResponsablesByArea($areaId);
    }

    /**
     * Obtiene responsables por olimpiada específica.
     *
     * @param int $olimpiadaId
     * @return array
     */
    public function getResponsablesByOlimpiada(int $olimpiadaId): array
    {
        return $this->responsableRepository->getResponsablesByOlimpiada($olimpiadaId);
    }

    /**
     * Obtiene las gestiones (olimpiadas) en las que ha trabajado un responsable.
     *
     * @param string $ci
     * @return array
     */
    public function getGestionesByCi(string $ci): array
    {
        return $this->responsableRepository->findGestionesByCi($ci);
    }

    /**
     * Obtiene las áreas asignadas a un responsable para una gestión específica.
     *
     * @param string $ci
     * @param string $gestion
     * @return array
     */
    public function getAreasByCiAndGestion(string $ci, string $gestion): array
    {
        return $this->responsableRepository->findAreasByCiAndGestion($ci, $gestion);
    }

    /**
     * Actualiza un responsable existente.
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function updateResponsable(int $id, array $data): array
    {
        return DB::transaction(function () use ($id, $data) {
            $usuario = $this->responsableRepository->updateUsuario($id, $data);

            if (isset($data['areas'])) {
                $this->responsableRepository->updateResponsableAreaRelations($usuario, $data['areas'], $data['id_olimpiada']);
            }

            return $this->getResponsableData($usuario);
        });
    }

    /**
     * Actualiza un responsable existente por su CI.
     *
     * @param string $ci
     * @param array $data
     * @return array|null
     */
    public function updateResponsableByCi(string $ci, array $data): ?array
    {
        $usuario = $this->responsableRepository->findUsuarioByCi($ci);

        if (!$usuario) {
            return null;
        }

        return DB::transaction(function () use ($usuario, $data) {
            $usuarioActualizado = $this->responsableRepository->updateUsuario($usuario->id_usuario, $data);

            if (isset($data['areas']) && isset($data['id_olimpiada'])) {
                $this->responsableRepository->updateResponsableAreaRelations($usuarioActualizado, $data['areas'], $data['id_olimpiada']);
            }

            return $this->getResponsableData($usuarioActualizado);
        });
    }

    /**
     * Añade nuevas áreas a un responsable existente por su CI.
     *
     * @param string $ci
     * @param array $data
     * @return array|null
     */
    public function addAreasToResponsableByCi(string $ci, array $data): ?array
    {
        $usuario = $this->responsableRepository->findUsuarioByCi($ci);

        if (!$usuario) {
            return null;
        }

        return DB::transaction(function () use ($usuario, $data) {
            $this->responsableRepository->addResponsableAreaRelations(
                $usuario,
                $data['areas'],
                $data['id_olimpiada']
            );
            return $this->getResponsableData($usuario->fresh());
        });
    }

    /**
     * Elimina un responsable.
     *
     * @param int $id
     * @return bool
     */
    public function deleteResponsable(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            return $this->responsableRepository->deleteResponsable($id);
        });
    }

    /**
     * Obtiene las áreas ocupadas por responsables en la gestión actual.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAreasOcupadasEnGestionActual()
    {
        $gestionActual = date('Y'); // Obtiene el año actual, ej: "2025"
        return $this->responsableRepository->getAreasOcupadasPorGestion($gestionActual);
    }


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
    private function getResponsableData(Usuario $usuario, ?array $responsableAreas = null): array
    {
        $usuario->loadMissing('responsableArea.area');
        if ($responsableAreas === null) {
            $responsableAreas = $usuario->responsableArea->toArray();
        }

        return [
            'id_usuario' => $usuario->id_usuario,
            'nombre' => $usuario->nombre,
            'apellido' => $usuario->apellido,
            'ci' => $usuario->ci,
            'email' => $usuario->email,
            'telefono' => $usuario->telefono,
            'rol' => 'Responsable Area',
            'areas_asignadas' => array_map(function ($ra) {
                return [
                    'id_area' => $ra['area']['id_area'],
                    'nombre_area' => $ra['area']['nombre']
                ];
            }, $responsableAreas),
            'created_at' => $usuario->created_at,
            'updated_at' => $usuario->updated_at
        ];
    }
}
