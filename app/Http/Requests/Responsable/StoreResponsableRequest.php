<?php

namespace App\Http\Requests\Responsable;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;

class StoreResponsableRequest extends FormRequest
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
            'telefono' => ['nullable', 'string', 'max:20', 'unique:persona,telefono'],

            'email'    => ['required', 'email', 'max:100', 'unique:usuario,email'],
            'password' => ['required', 'string', 'min:8'],

            'id_olimpiada' => ['required', 'integer', 'exists:olimpiada,id_olimpiada'],

            'areas'   => ['required', 'array', 'min:1'],
            'areas.*' => [
                'integer',
                'exists:area,id_area',
                function ($attribute, $value, $fail) {
                    $exists = DB::table('area_olimpiada')
                        ->where('id_area', $value)
                        ->where('id_olimpiada', $this->id_olimpiada)
                        ->exists();

                    if (!$exists) {
                        $fail("El área ID {$value} no está habilitada para la olimpiada seleccionada.");
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
            'errors'  => [$campo => [$mensaje]]
        ], 422));
    }

    public function messages(): array
    {
        return [
            'ci.unique'       => 'Este usuario ya está registrado con este CI.',
            'email.unique'    => 'Un usuario ya está manejando este correo electrónico.',
            'telefono.unique' => 'Un usuario ya está manejando este número de teléfono.',
            'areas.required'  => 'Debe asignar al menos un área de conocimiento.',
        ];
    }
}
