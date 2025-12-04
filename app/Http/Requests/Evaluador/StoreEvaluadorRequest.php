<?php

namespace App\Http\Requests\Evaluador;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;

class StoreEvaluadorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'nombre'   => ['required', 'string', 'max:50'],
            'apellido' => ['required', 'string', 'max:50'],

            'ci'       => ['required', 'string', 'max:20', 'unique:persona,ci'],
            'email'    => ['required', 'email', 'max:100', 'unique:usuario,email'],
            'telefono' => ['nullable', 'string', 'max:20', 'unique:persona,telefono'],

            'password' => ['required', 'string', 'min:8'],
            'id_olimpiada'   => ['required', 'integer', 'exists:olimpiada,id_olimpiada'],
            'area_nivel_ids' => ['required', 'array', 'min:1'],
            'area_nivel_ids.*' => [
                'integer',
                'exists:area_nivel,id_area_nivel',

                function ($attribute, $value, $fail) {
                    $exists = DB::table('area_nivel')
                        ->join('area_olimpiada', 'area_nivel.id_area_olimpiada', '=', 'area_olimpiada.id_area_olimpiada')
                        ->where('area_nivel.id_area_nivel', $value)
                        ->where('area_olimpiada.id_olimpiada', $this->id_olimpiada)
                        ->exists();

                    if (!$exists) {
                        $fail("El área-nivel ID {$value} no es válido para la olimpiada seleccionada.");
                    }
                }
            ],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        if ($errors->has('ci')) {
            $this->lanzarErrorUnico('ci', $errors->first('ci'));
        }

        if ($errors->has('email')) {
            $this->lanzarErrorUnico('email', $errors->first('email'));
        }

        if ($errors->has('telefono')) {
            $this->lanzarErrorUnico('telefono', $errors->first('telefono'));
        }

        parent::failedValidation($validator);
    }

    private function lanzarErrorUnico($campo, $mensaje)
    {
        throw new HttpResponseException(response()->json([
            'message' => $mensaje,
            'errors'  => [
                $campo => [$mensaje]
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
