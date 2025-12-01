<?php

namespace App\Http\Requests\Departamento;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:20', 'unique:departamento,nombre'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del departamento es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder los 20 caracteres.',
            'nombre.unique' => 'Este departamento ya está registrado.',
        ];
    }
}
