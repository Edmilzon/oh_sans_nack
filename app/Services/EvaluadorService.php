<?php

namespace App\Services;

use App\Repositories\EvaluadorRepository;
use App\Model\Usuario;
use App\Mail\UserCredentialsMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class EvaluadorService
{
    public function __construct(
        protected EvaluadorRepository $repo
    ) {}

    public function createEvaluador(array $data): array
    {
        return DB::transaction(function () use ($data) {

            $persona = $this->repo->findOrCreatePersona($data);

            $usuario = $this->repo->createUsuario($persona, $data);

            $this->repo->assignEvaluadorRole($usuario, $data['id_olimpiada']);

            $this->repo->syncEvaluadorAreas($usuario, $data['area_nivel_ids']);

            $this->sendCredentialsEmail($usuario, $data['password']);

            return $this->repo->getById($usuario->id_usuario);
        });
    }

    public function addAsignacionesToEvaluador(string $ci, int $idOlimpiada, array $areaNivelIds): array
    {
        return DB::transaction(function () use ($ci, $idOlimpiada, $areaNivelIds) {

            $usuario = Usuario::whereHas('persona', function ($query) use ($ci) {
                $query->where('ci', $ci);
            })->first();

            if (!$usuario) {
                throw new Exception("No se encontró ningún usuario con el CI: {$ci}");
            }

            $this->repo->assignEvaluadorRole($usuario, $idOlimpiada);

            $this->repo->syncEvaluadorAreas($usuario, $areaNivelIds);

            return [
                'id_usuario' => $usuario->id_usuario,
                'nombre'     => $usuario->persona->nombre . ' ' . $usuario->persona->apellido,
                'nuevas_asignaciones' => count($areaNivelIds),
                'mensaje'    => 'Asignaciones actualizadas correctamente.'
            ];
        });
    }

    public function getEvaluadorById(int $id): ?array
    {
        return $this->repo->getById($id);
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
                        'Evaluador'                
                    )
                );
            }
        } catch (\Throwable $e) {
            Log::error("Error enviando correo de bienvenida al usuario {$usuario->id_usuario}: " . $e->getMessage());
        }
    }
}
