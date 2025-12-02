<?php

namespace App\Http\Requests\Fase;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConfiguracionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Aquí podrías agregar lógica de roles (ej: solo Admin),
        // por ahora retornamos true para permitir el acceso.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Validamos que venga el array principal
            'accionesPorFase' => ['required', 'array', 'min:1'],

            // Validamos cada elemento dentro del array
            'accionesPorFase.*.idAccion' => ['required', 'integer', 'exists:accion_sistema,id_accion_sistema'],
            'accionesPorFase.*.idFase'   => ['required', 'integer', 'exists:fase_global,id_fase_global'],
            'accionesPorFase.*.habilitada' => ['required', 'boolean'],
        ];
    }

    /**
     * Mensajes personalizados para errores de validación.
     */
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
