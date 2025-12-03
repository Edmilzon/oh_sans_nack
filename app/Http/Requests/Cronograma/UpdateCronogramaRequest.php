<?php

namespace App\Http\Requests\Cronograma;

use Illuminate\Foundation\Http\FormRequest;
use App\Model\CronogramaFase; // Importamos el modelo

class UpdateCronogramaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // 1. Capturamos el parámetro de la ruta.
        // Laravel suele nombrar el parámetro singular basado en el resource ('cronograma_fase').
        // Probamos ambas opciones por seguridad.
        $rutaParam = $this->route('cronograma_fase') ?? $this->route('cronograma');

        $cronograma = null;

        // 2. Verificamos qué nos llegó
        if ($rutaParam instanceof CronogramaFase) {
            // Caso A: Laravel ya hizo el binding, tenemos el objeto
            $cronograma = $rutaParam;
        } elseif (is_numeric($rutaParam)) {
            // Caso B: Nos llegó solo el ID (string/int), buscamos manualmente
            $cronograma = CronogramaFase::find($rutaParam);
        }

        // 3. Si logramos obtener el modelo, aplicamos la lógica de fechas
        if ($cronograma) {
            $this->merge([
                // Si el usuario no envía fecha, usamos la que ya tiene el modelo en BD
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
                // Ahora esto funcionará porque 'fecha_inicio' siempre existirá en el request
                // gracias al prepareForValidation
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
