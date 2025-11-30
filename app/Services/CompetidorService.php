<?php

namespace App\Services;

use App\Model\Persona;
use App\Model\Inscripcion; // Nuevo modelo requerido
use App\Repositories\CompetidorRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompetidorService
{
    protected $competidorRepository;

    public function __construct(CompetidorRepository $competidorRepository)
    {
        $this->competidorRepository = $competidorRepository;
    }

    /**
     * Crea un nuevo competidor, su registro de Persona asociado, y su Inscripción.
     *
     * @param array $data Contiene datos de Persona, Competidor y id_area_nivel.
     * @return Persona
     */
    public function createNewCompetidor(array $data): Persona
    {
        return DB::transaction(function () use ($data) {

            // 1. Crear Persona (Repositorio usa sufijos _pers)
            $persona = $this->competidorRepository->createPersona([
                'nombre' => $data['nombre'],
                'apellido' => $data['apellido'],
                'ci' => $data['ci'],
                'genero' => $data['genero'],
                'telefono' => $data['telefono'] ?? null,
                'email' => $data['email'],
            ]);

            // 2. Crear Competidor (Enlazado a Persona)
            // NOTA: Asumo que los IDs de grado y departamento vienen como IDs enteros.
            $competidor = $this->competidorRepository->createCompetidor([
                'id_persona' => $persona->id_persona,
                'id_grado_escolaridad' => $data['id_grado_escolaridad'], // Corregido el nombre de campo
                'id_departamento' => $data['id_departamento'],         // Corregido el nombre de campo
                'contacto_tutor_compe' => $data['contacto_tutor'] ?? null, // Columna corregida
                'id_institucion' => $data['id_institucion'],
                'id_archivo_csv' => $data['id_archivo_csv'],
                'genero_competidor' => $data['genero'], // Columna corregida (redundante, pero necesario si no se usa FK de persona)
            ]);

            // 3. **CRÍTICO:** Crear Inscripción para enlazar al AreaNivel (necesario para la Evaluación)
            Inscripcion::create([
                'id_competidor' => $competidor->id_competidor,
                'id_area_nivel' => $data['id_area_nivel'],
            ]);

            // Devolver la persona con la relación competidor cargada
            return $persona->load(['competidor']);
        });
    }

    // Nota: Aquí faltarían métodos como procesarImportacionIndividual si la lógica
    // de la importación masiva no fue movida al CompetidorRepository.
}
