<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre_archivo' => ['required', 'string', 'max:255'],
            'competidores' => ['required', 'array', 'min:1'],

            // Validamos estructura, no existencia en BD (eso lo hace el Service por rendimiento)
            'competidores.*.persona.nombre' => ['required', 'string', 'max:255'],
            'competidores.*.persona.apellido' => ['required', 'string', 'max:255'],
            'competidores.*.persona.ci' => ['required', 'string', 'max:20'],
            'competidores.*.persona.genero' => ['required', 'string', 'in:M,F'],
            'competidores.*.persona.email' => ['required', 'email'],
            'competidores.*.persona.telefono' => ['nullable', 'string', 'max:15'],

            'competidores.*.competidor.grado_escolar' => ['required', 'string'],
            'competidores.*.competidor.departamento' => ['required', 'string'],
            'competidores.*.competidor.contacto_tutor' => ['nullable', 'string'],

            'competidores.*.institucion.nombre' => ['required', 'string'],
            'competidores.*.area.nombre' => ['required', 'string'],
            'competidores.*.nivel.nombre' => ['required', 'string'],
        ];
    }
}
