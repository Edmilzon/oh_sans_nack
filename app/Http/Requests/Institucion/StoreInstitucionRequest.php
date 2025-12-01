<?php

namespace App\Http\Requests\Institucion;

use Illuminate\Foundation\Http\FormRequest;

class StoreInstitucionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:250', 'unique:institucion,nombre'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la institución es obligatorio.',
            'nombre.max'      => 'El nombre no puede exceder los 250 caracteres.',
            'nombre.unique'   => 'Esta institución ya está registrada.',
        ];
    }
}
