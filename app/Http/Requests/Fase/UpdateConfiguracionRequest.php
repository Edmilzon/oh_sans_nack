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

            'accionesPorFase' => ['required', 'array', 'min:1'],

            'accionesPorFase.*.idAccion' => ['required', 'integer', 'exists:accion_sistema,id_accion_sistema'],
            'accionesPorFase.*.idFase'   => ['required', 'integer', 'exists:fase_global,id_fase_global'],
            'accionesPorFase.*.habilitada' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'accionesPorFase.required' => 'No se han enviado datos de configuración.',
            'accionesPorFase.array'    => 'El formato de los datos de configuración es inválido.',
            'accionesPorFase.min'      => 'Debe enviar al menos una configuración para guardar.',
            'accionesPorFase.*.idAccion.exists' => 'Una de las acciones enviadas no existe en el sistema.',
            'accionesPorFase.*.idFase.exists'   => 'Una de las fases globales enviadas no existe.',
            'accionesPorFase.*.habilitada.boolean' => 'El valor de habilitado debe ser verdadero o falso.',
        ];
    }
}
