<?php

namespace App\Http\Requests\Fase;

use Illuminate\Foundation\Http\FormRequest;

class StoreFaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'       => ['required', 'string', 'max:255'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin'    => ['required', 'date', 'after_or_equal:fecha_inicio'],
            // 'orden' no es crítico en competencia, pero lo aceptamos si viene
            'orden'        => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'       => 'El nombre del examen/fase es obligatorio.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
        ];
    }
}
