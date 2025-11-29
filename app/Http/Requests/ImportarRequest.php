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
            'nombre_archivo' => 'required|string|max:255',
            'competidores' => 'required|array|min:1',
            
            // Datos de Persona (obligatorios según especificación)
            'competidores.*.persona.nombre' => 'required|string|max:255',
            'competidores.*.persona.apellido' => 'required|string|max:255',
            'competidores.*.persona.ci' => 'required|string|max:20',
            'competidores.*.persona.genero' => 'required|in:M,F',
            'competidores.*.persona.telefono' => 'nullable|string|max:15',
            'competidores.*.persona.email' => 'required|email',

            // Datos del Competidor (obligatorios según especificación)
            'competidores.*.competidor.grado_escolar' => 'required|string|max:100',
            'competidores.*.competidor.departamento' => 'required|string|max:100',
            'competidores.*.competidor.contacto_tutor' => 'nullable|string|max:255',

            // Datos de Institución (obligatorio según especificación)
            'competidores.*.institucion.nombre' => 'required|string|max:255',


            // Datos Relacionales (obligatorios según especificación)
            'competidores.*.area.nombre' => 'required|string|max:255',
            'competidores.*.nivel.nombre' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'competidores.*.persona.nombre.required' => 'El nombre es obligatorio',
            'competidores.*.persona.apellido.required' => 'El apellido es obligatorio',
            'competidores.*.persona.ci.required' => 'El documento de identidad es obligatorio',
            'competidores.*.persona.genero.required' => 'El género es obligatorio',
            'competidores.*.persona.email.required' => 'El email es obligatorio',
            'competidores.*.competidor.grado_escolar.required' => 'El grado escolar es obligatorio',
            'competidores.*.competidor.departamento.required' => 'El departamento es obligatorio',
            'competidores.*.institucion.nombre.required' => 'El nombre de la institución es obligatorio',
            'competidores.*.area.nombre.required' => 'El área es obligatoria',
            'competidores.*.nivel.nombre.required' => 'El nivel es obligatorio',
        ];
    }
}