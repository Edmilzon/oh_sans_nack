<?php

namespace App\Http\Requests\GradoEscolaridad;

use Illuminate\Foundation\Http\FormRequest;

class StoreGradoEscolaridadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255', 'unique:grado_escolaridad,nombre'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del grado es obligatorio.',
            'nombre.unique'   => 'Este grado escolar ya existe en el sistema.',
        ];
    }
}
