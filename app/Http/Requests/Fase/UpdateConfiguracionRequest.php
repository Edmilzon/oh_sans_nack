<?php

namespace App\Http\Requests\Fase;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConfiguracionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'accionesPorFase' => ['required', 'array'],
            'accionesPorFase.*.idAccion' => ['required', 'integer', 'exists:accion_sistema,id_accion_sistema'],
            'accionesPorFase.*.idFase'   => ['required', 'integer', 'exists:fase_global,id_fase_global'],
            'accionesPorFase.*.habilitada' => ['required', 'boolean'],
        ];
    }
}
