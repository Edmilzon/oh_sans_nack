<?php

namespace App\Services;

use App\Repositories\ResponsableRepository;
use App\Model\Usuario;
use App\Mail\UserCredentialsMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class ResponsableService
{
    public function __construct(
        protected ResponsableRepository $repo
    ) {}

    /**
     * Crea un Responsable completo.
     */
    public function createResponsable(array $data): array
    {
        return DB::transaction(function () use ($data) {

            // 1. Gestionar Persona (Update or Create)
            $persona = $this->repo->findOrCreatePersona($data);

            // 2. Crear Usuario
            $usuario = $this->repo->createUsuario($persona, $data);

            // 3. Asignar Rol
            $this->repo->assignResponsableRole($usuario, $data['id_olimpiada']);

            // 4. Asignar Áreas
            // Nota: $data['areas'] es un array de IDs de área (ej: [1, 2])
            $this->repo->syncResponsableAreas($usuario, $data['areas'], $data['id_olimpiada']);

            // 5. Enviar Credenciales (Fail-Safe)
            $this->sendCredentialsEmail($usuario, $data['password']);

            return $this->repo->getById($usuario->id_usuario);
        });
    }

    public function getAll(): array
    {
        return $this->repo->getAllResponsables()->toArray();
    }

    public function getById(int $id): ?array
    {
        return $this->repo->getById($id);
    }

    /**
     * Lógica para el Escenario 3 (Agregar áreas a responsable existente)
     */
    public function addAreasToResponsable(string $ci, int $idOlimpiada, array $areaIds): array
    {
        return DB::transaction(function () use ($ci, $idOlimpiada, $areaIds) {

            // Buscar usuario por CI
            $usuario = Usuario::whereHas('persona', fn($q) => $q->where('ci', $ci))->first();

            if (!$usuario) {
                throw new Exception("No se encontró ningún usuario con el CI: {$ci}");
            }

            // Asegurar rol
            $this->repo->assignResponsableRole($usuario, $idOlimpiada);

            // Agregar nuevas áreas
            $this->repo->syncResponsableAreas($usuario, $areaIds, $idOlimpiada);

            return [
                'id_usuario' => $usuario->id_usuario,
                'nombre'     => $usuario->persona->nombre . ' ' . $usuario->persona->apellido,
                'mensaje'    => 'Áreas asignadas correctamente.'
            ];
        });
    }

    private function sendCredentialsEmail(Usuario $usuario, string $rawPassword): void
    {
        try {
            if (!empty($usuario->email)) {
                Mail::to($usuario->email)->queue(
                    new UserCredentialsMail(
                        $usuario->persona->nombre,
                        $usuario->email,
                        $rawPassword,
                        'Responsable de Área'
                    )
                );
            }
        } catch (\Throwable $e) {
            Log::error("Error enviando correo a responsable {$usuario->id_usuario}: " . $e->getMessage());
        }
    }
}
