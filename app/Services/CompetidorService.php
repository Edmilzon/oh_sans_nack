<?php

namespace App\Services;

use App\Model\Persona;
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

    public function createNewCompetidor(array $data): Persona
    {
        return DB::transaction(function () use ($data) {
            $persona = $this->competidorRepository->createPersona([
                'nombre' => $data['nombre'],
                'apellido' => $data['apellido'],
                'ci' => $data['ci'],
                'genero' => $data['genero'],
                'telefono' => $data['telefono'] ??null,
                'email' => $data['email'],
            ]);

            $competidor = $this->competidorRepository->createCompetidor([
                'id_persona' => $persona->id_persona,
                'grado_escolar' => $data['grado_escolar'],        
                'departamento' => $data['departamento'],    
                'contacto_tutor' => $data['contacto_tutor'] ?? null,
                'id_institucion' => $data['id_institucion'],
                'id_area_nivel' => $data['id_area_nivel'],
                'id_archivo_csv' => $data['id_archivo_csv'],         
            ]);

            return $persona->load(['competidor']);
        });
    }
}