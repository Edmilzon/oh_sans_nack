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
use Exception;

class ResponsableService
{
    protected $responsableRepository;

    public function __construct(ResponsableRepository $responsableRepository)
    {
        $this->responsableRepository = $responsableRepository;
    }

    /**
     * Crea un nuevo responsable de área (Persona + Usuario).
     *
     * @param array $data Contiene 'password' en texto plano.
     * @return array
     * @throws \Exception
     */
    public function createResponsable(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $plainPassword = $data['password'];

            // 1. Crear Usuario (Repositorio crea Persona + Usuario y hashea la password)
            $usuario = $this->responsableRepository->createUsuario($data);

            // 2. Asignar rol
            $this->responsableRepository->assignResponsableRole($usuario, $data['id_olimpiada']);

            // 3. Crear relaciones con las áreas
            $responsableAreas = $this->responsableRepository->createResponsableAreaRelations(
                $usuario,
                $data['areas'],
                $data['id_olimpiada']
            );

            // 4. Enviar correo (Usando las columnas de la BD V8)
            Mail::to($usuario->email_usuario)->send(new UserCredentialsMail( // Columna corregida
                $usuario->persona->nombre_pers, // Acceso corregido
                $usuario->email_usuario,       // Columna corregida
                $plainPassword,                // Contraseña en texto plano
                'Responsable de Área'
            ));

            // Retornar la data mapeada
            return $this->getResponsableData($usuario->fresh(['persona', 'responsableArea.areaOlimpiada.area']));
        });
    }

    /**
     * Obtiene todos los responsables de área.
     */
    public function getAllResponsables(): array
    {
        // El Repositorio ya retorna la data mapeada
        return $this->responsableRepository->getAllResponsablesWithAreas();
    }

    /**
     * Obtiene un responsable específico por ID.
     */
    public function getResponsableById(int $id): ?array
    {
        // El Repositorio ya retorna la data mapeada
        return $this->responsableRepository->getResponsableByIdWithAreas($id);
    }

    /**
     * Obtiene responsables por área específica.
     */
    public function getResponsablesByArea(int $areaId): array
    {
        // El Repositorio ya retorna la data mapeada
        return $this->responsableRepository->getResponsablesByArea($areaId);
    }

    /**
     * Obtiene responsables por olimpiada específica.
     */
    public function getResponsablesByOlimpiada(int $olimpiadaId): array
    {
        // El Repositorio ya retorna la data mapeada
        return $this->responsableRepository->getResponsablesByOlimpiada($olimpiadaId);
    }

    /**
     * Obtiene las gestiones (olimpiadas) en las que ha trabajado un responsable.
     */
    public function getGestionesByCi(string $ci): array
    {
        return $this->responsableRepository->findGestionesByCi($ci);
    }

    /**
     * Obtiene las áreas asignadas a un responsable para una gestión específica.
     */
    public function getAreasByCiAndGestion(string $ci, string $gestion): array
    {
        return $this->responsableRepository->findAreasByCiAndGestion($ci, $gestion);
    }

    /**
     * Actualiza un responsable existente.
     */
    public function updateResponsable(int $id, array $data): array
    {
        return DB::transaction(function () use ($id, $data) {
            // El Repositorio actualiza Persona y Usuario
            $usuario = $this->responsableRepository->updateUsuario($id, $data);

            if (isset($data['areas']) && isset($data['id_olimpiada'])) {
                $this->responsableRepository->updateResponsableAreaRelations($usuario, $data['areas'], $data['id_olimpiada']);
            }

            // Retornar la data mapeada
            return $this->getResponsableData($usuario->fresh(['persona', 'responsableArea.areaOlimpiada.area']));
        });
    }

    /**
     * Actualiza un responsable existente por su CI.
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
            return $this->getResponsableData($usuario->fresh(['persona', 'responsableArea.areaOlimpiada.area']));
        });
    }

    /**
     * Elimina un responsable.
     */
    public function deleteResponsable(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            return $this->responsableRepository->deleteResponsable($id);
        });
    }

    /**
     * Obtiene las áreas ocupadas por responsables en la gestión actual.
     */
    public function getAreasOcupadasEnGestionActual()
    {
        $gestionActual = date('Y');
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
     * Mapea los campos de Persona y las relaciones anidadas a la estructura simple de salida.
     *
     * @param Usuario $usuario
     * @param array|null $responsableAreas (No utilizado si se carga la relación 'responsableArea')
     * @return array
     */
    private function getResponsableData(Usuario $usuario, ?array $responsableAreas = null): array
    {
        // 1. Asegurar la carga de relaciones (ya cargadas en los repositorios, pero como fallback)
        $usuario->loadMissing('persona', 'responsableArea.areaOlimpiada.area');

        // 2. Mapear áreas asignadas
        $areasAsignadas = $usuario->responsableArea->map(function ($ra) {
            $area = $ra->areaOlimpiada->area ?? null;

            if (!$area) return null;

            return [
                'id_area' => $area->id_area,
                'nombre_area' => $area->nombre_area // Columna corregida
            ];
        })->filter()->values()->toArray();

        // 3. Devolver el JSON con claves de la V7
        return [
            'id_usuario' => $usuario->id_usuario,
            // Mapeo Persona/Usuario -> Frontend keys
            'nombre' => $usuario->persona->nombre_pers,
            'apellido' => $usuario->persona->apellido_pers,
            'ci' => $usuario->persona->ci_pers,
            'email' => $usuario->email_usuario, // Columna corregida
            'telefono' => $usuario->persona->telefono_pers ?? null, // Columna corregida

            'rol' => 'Responsable Area',
            'areas_asignadas' => $areasAsignadas,

            'created_at' => $usuario->created_at,
            'updated_at' => $usuario->updated_at
        ];
    }
}
