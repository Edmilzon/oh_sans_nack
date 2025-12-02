<?php

namespace App\Services;

use App\Repositories\ResponsableRepository;
use App\Model\Usuario;
use App\Model\Olimpiada;
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

    public function createResponsable(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $persona = $this->repo->findOrCreatePersona($data);
            $usuario = $this->repo->createUsuario($persona, $data);

            $this->repo->assignResponsableRole($usuario, $data['id_olimpiada']);
            $this->repo->syncResponsableAreas($usuario, $data['areas'], $data['id_olimpiada']);

            $this->sendCredentialsEmail($usuario, $data['password']);

            return $this->repo->getById($usuario->id_usuario);
        });
    }

    public function updateResponsable(string $ci, array $data): array
    {
        return DB::transaction(function () use ($ci, $data) {
            $usuario = $this->repo->getByCi($ci);
            if (!$usuario) throw new Exception("Usuario no encontrado con CI: $ci");

            $this->repo->updateResponsable($usuario, $data);

            // Si envían nuevas áreas o cambio de gestión, se maneja aquí
            if (isset($data['id_olimpiada']) && isset($data['areas'])) {
                $this->repo->assignResponsableRole($usuario, $data['id_olimpiada']);
                $this->repo->syncResponsableAreas($usuario, $data['areas'], $data['id_olimpiada']);
            }

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

    public function addAreasToResponsable(string $ci, int $idOlimpiada, array $areaIds): array
    {
        return DB::transaction(function () use ($ci, $idOlimpiada, $areaIds) {
            $usuario = $this->repo->getByCi($ci);
            if (!$usuario) throw new Exception("No se encontró usuario con CI: {$ci}");

            $this->repo->assignResponsableRole($usuario, $idOlimpiada);
            $this->repo->syncResponsableAreas($usuario, $areaIds, $idOlimpiada);

            return [
                'id_usuario' => $usuario->id_usuario,
                'nombre'     => $usuario->persona->nombre,
                'mensaje'    => 'Áreas asignadas correctamente.'
            ];
        });
    }

    // Método para el Escenario 2/3 (Historial de Gestiones)
    public function getGestionesByCi(string $ci)
    {
        $usuario = $this->repo->getByCi($ci);
        if (!$usuario) return [];

        return $this->repo->getGestionesByUsuario($usuario->id_usuario);
    }

    // Método para el Escenario 2/3 (Historial de Áreas por Gestión)
    public function getAreasByCiAndGestion(string $ci, string $gestion)
    {
        $usuario = $this->repo->getByCi($ci);
        if (!$usuario) return [];

        return $this->repo->getAreasByUsuarioAndGestion($usuario->id_usuario, $gestion);
    }

    public function getAreasOcupadasEnGestionActual()
    {
        $olimpiadaActual = Olimpiada::where('estado', true)->latest('gestion')->first();
        if (!$olimpiadaActual) return [];

        $areas = $this->repo->getAreasOcupadasPorGestion($olimpiadaActual->id_olimpiada);
        return $areas->map(function($area) {
            return [
                'id_area' => $area->id_area,
                'nombre'  => $area->nombre
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
            Log::error("Error mail responsable: " . $e->getMessage());
        }
    }
}
