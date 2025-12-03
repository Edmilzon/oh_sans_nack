<?php

namespace App\Http\Requests\Reporte;

use Illuminate\Foundation\Http\FormRequest;

class GetHistorialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['required', 'integer', 'min:1'],
            'limit' => ['required', 'integer', 'min:1', 'max:100000'],
            'id_area' => ['nullable', 'integer', 'exists:area,id_area'],
            'ids_niveles' => ['nullable', 'string'],
            'search' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'page.required' => 'El número de página es obligatorio.',
            'limit.required' => 'El límite de registros es obligatorio.',
            'id_area.exists' => 'El área seleccionada no es válida.',
        ];
    }

    /**
     * Prepara los datos antes de la validación.
     * Útil si envían ids_niveles como array y lo queremos validar o viceversa.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'page' => $this->input('page', 1),
            'limit' => $this->input('limit', 10),
        ]);
    }
}
