<?php

namespace App\Http\Requests\Evaluador;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreEvaluadorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Datos Personales
            'nombre'   => ['required', 'string', 'max:50'],
            'apellido' => ['required', 'string', 'max:50'],

            // Reglas estrictas de unicidad
            'ci'       => ['required', 'string', 'max:20', 'unique:persona,ci'],
            'email'    => ['required', 'email', 'max:100', 'unique:usuario,email'],
            'telefono' => ['nullable', 'string', 'max:20', 'unique:persona,telefono'],

            'password' => ['required', 'string', 'min:8'],
            'id_olimpiada'   => ['required', 'integer', 'exists:olimpiada,id_olimpiada'],
            'area_nivel_ids' => ['required', 'array', 'min:1'],
            'area_nivel_ids.*' => ['integer', 'exists:area_nivel,id_area_nivel'],
        ];
    }

    /**
     * Sobrescribimos este método para controlar la jerarquía de errores.
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        // JERARQUÍA 1: Cédula de Identidad (El error más crítico)
        // Si el CI ya existe, detenemos todo y solo mostramos esto.
        if ($errors->has('ci')) {
            $this->lanzarErrorUnico('ci', $errors->first('ci'));
        }

        // JERARQUÍA 2: Correo Electrónico
        // Si el CI pasó, pero el correo está repetido, mostramos solo esto.
        if ($errors->has('email')) {
            $this->lanzarErrorUnico('email', $errors->first('email'));
        }

        // JERARQUÍA 3: Teléfono
        // Si CI y Email pasaron, pero el teléfono está repetido.
        if ($errors->has('telefono')) {
            $this->lanzarErrorUnico('telefono', $errors->first('telefono'));
        }

        // Si son otros errores (campos vacíos, contraseña corta, etc.),
        // dejamos que Laravel muestre la lista completa normal.
        parent::failedValidation($validator);
    }

    /**
     * Helper para lanzar una respuesta JSON limpia con un solo error.
     */
    private function lanzarErrorUnico($campo, $mensaje)
    {
        throw new HttpResponseException(response()->json([
            'message' => $mensaje, // Mensaje principal limpio
            'errors'  => [
                $campo => [$mensaje] // Estructura estándar de Laravel pero con un solo campo
            ]
        ], 422));
    }

    public function messages(): array
    {
        return [
            'ci.unique'       => 'Este usuario ya está registrado con este CI.',
            'email.unique'    => 'Un usuario ya está manejando este correo electrónico.',
            'telefono.unique' => 'Un usuario ya está manejando este número de teléfono.',
            'ci.required'     => 'El CI es obligatorio.',
            'email.required'  => 'El correo es obligatorio.',
        ];
    }
}
