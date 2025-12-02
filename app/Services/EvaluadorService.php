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

    /**
     * Crea un Evaluador desde cero (Persona + Usuario + Rol + Asignaciones).
     * * @param array $data Datos validados del Request
     * @return array Estructura del evaluador creado para el frontend
     * @throws Exception
     */
    public function createEvaluador(array $data): array
    {
        return DB::transaction(function () use ($data) {

            // 1. Gestionar Persona (Busca por CI o Crea nueva)
            // Esto permite que una persona que ya era "Responsable" ahora sea también "Evaluador"
            $persona = $this->repo->findOrCreatePersona($data);

            // 2. Crear el Usuario vinculado a esa Persona
            $usuario = $this->repo->createUsuario($persona, $data);

            // 3. Asignar el Rol 'Evaluador' específicamente para esta Olimpiada
            // La tabla pivote 'usuario_rol' recibe 'id_olimpiada'
            $this->repo->assignEvaluadorRole($usuario, $data['id_olimpiada']);

            // 4. Asignar las Áreas y Niveles que va a evaluar (tabla 'evaluador_an')
            $this->repo->syncEvaluadorAreas($usuario, $data['area_nivel_ids']);

            // 5. Enviar Credenciales por Correo (Fail-Safe)
            // Lo hacemos dentro de un try/catch para que si falla el correo, NO se revierta el registro del usuario.
            $this->sendCredentialsEmail($usuario, $data['password']);

            // 6. Retornar el objeto formateado tal cual lo espera el Frontend
            return $this->repo->getById($usuario->id_usuario);
        });
    }

    /**
     * Agrega nuevas áreas/niveles a un Evaluador existente.
     * (Escenario 3: El usuario ya existe, solo le damos más permisos).
     * * @param string $ci Cédula del evaluador
     * @param int $idOlimpiada Gestión donde se asigna
     * @param array $areaNivelIds Lista de IDs de area_nivel
     * @return array Resumen de la operación
     */
    public function addAsignacionesToEvaluador(string $ci, int $idOlimpiada, array $areaNivelIds): array
    {
        return DB::transaction(function () use ($ci, $idOlimpiada, $areaNivelIds) {

            // 1. Buscar al Usuario por su CI
            // Usamos una consulta directa o un método del repo si existiera.
            $usuario = Usuario::whereHas('persona', function ($query) use ($ci) {
                $query->where('ci', $ci);
            })->first();

            if (!$usuario) {
                throw new Exception("No se encontró ningún usuario con el CI: {$ci}");
            }

            // 2. Asegurar que tenga el Rol 'Evaluador' en esta Olimpiada
            // (Puede que sea un evaluador antiguo que vuelve este año)
            $this->repo->assignEvaluadorRole($usuario, $idOlimpiada);

            // 3. Agregar las nuevas áreas (Sincronización incremental)
            $this->repo->syncEvaluadorAreas($usuario, $areaNivelIds);

            // Retornamos datos básicos confirmando el éxito
            return [
                'id_usuario' => $usuario->id_usuario,
                'nombre'     => $usuario->persona->nombre . ' ' . $usuario->persona->apellido,
                'nuevas_asignaciones' => count($areaNivelIds),
                'mensaje'    => 'Asignaciones actualizadas correctamente.'
            ];
        });
    }

    /**
     * Obtiene el detalle de un evaluador por su ID.
     */
    public function getEvaluadorById(int $id): ?array
    {
        return $this->repo->getById($id);
    }

    /**
     * Helper privado para enviar correos sin detener el flujo.
     */
    private function sendCredentialsEmail(Usuario $usuario, string $rawPassword): void
    {
        try {
            if (!empty($usuario->email)) {
                // Asegúrate de tener la clase Mailable creada: App\Mail\UserCredentialsMail
                Mail::to($usuario->email)->queue(
                    new UserCredentialsMail(
                        $usuario->persona->nombre, // Nombre
                        $usuario->email,           // Email
                        $rawPassword,              // Password
                        'Evaluador'                // Rol
                    )
                );
            }
        } catch (\Throwable $e) {
            // Solo logueamos el error, no detenemos la transacción.
            Log::error("Error enviando correo de bienvenida al usuario {$usuario->id_usuario}: " . $e->getMessage());
        }
    }
}
