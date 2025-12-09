<?php

namespace App\Http\Requests\Cronograma;

use Illuminate\Foundation\Http\FormRequest;

class StoreCronogramaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_fase_global' => ['required', 'integer', 'exists:fase_global,id_fase_global'],
            'fecha_inicio'   => ['required', 'date'],
            'fecha_fin'      => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'descripcion'    => ['nullable', 'string', 'max:255'],
        ];
    }
}
