<?php

namespace App\Http\Requests\Cronograma;

use Illuminate\Foundation\Http\FormRequest;
use App\Model\CronogramaFase;

class UpdateCronogramaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $rutaParam = $this->route('cronograma_fase') ?? $this->route('cronograma');

        $cronograma = null;

        if ($rutaParam instanceof CronogramaFase) {

            $cronograma = $rutaParam;
        } elseif (is_numeric($rutaParam)) {

            $cronograma = CronogramaFase::find($rutaParam);
        }

        if ($cronograma) {
            $this->merge([

                'fecha_inicio' => $this->input('fecha_inicio', $cronograma->fecha_inicio),
                'fecha_fin'    => $this->input('fecha_fin', $cronograma->fecha_fin),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'id_fase_global' => ['sometimes', 'integer', 'exists:fase_global,id_fase_global'],

            'fecha_inicio' => ['sometimes', 'date'],

            'fecha_fin' => [
                'sometimes',
                'date',

                'after_or_equal:fecha_inicio'
            ],

            'descripcion' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_fin.after_or_equal' => 'La fecha de finalización no puede ser anterior a la fecha de inicio.',
        ];
    }
}
