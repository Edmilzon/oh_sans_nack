<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreParametroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'area_niveles' => ['required', 'array', 'min:1'],
            'area_niveles.*.id_area_nivel' => ['required', 'integer', 'exists:area_nivel,id_area_nivel'],
            // Ajustado a tus nombres de columnas reales
            'area_niveles.*.nota_min_aprobacion' => ['required', 'numeric', 'min:0', 'max:100'],
            'area_niveles.*.cantidad_maxima' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'area_niveles.required' => 'Debe enviar al menos una configuración de parámetros.',
            'area_niveles.*.id_area_nivel.exists' => 'El Área-Nivel seleccionado no es válido.',
            'area_niveles.*.nota_min_aprobacion.required' => 'La nota mínima es obligatoria.',
            'area_niveles.*.nota_min_aprobacion.min' => 'La nota mínima no puede ser negativa.',
            'area_niveles.*.cantidad_maxima.min' => 'La cantidad máxima no puede ser negativa.',
        ];
    }
}
