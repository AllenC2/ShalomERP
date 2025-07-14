<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ComisioneRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
            'contrato_id' => 'nullable', // Relación con el contrato
            'empleado_id' => 'nullable', // Empleado asociado a la comisión
            'fecha_comision' => 'nullable|date', // Fecha en que se generó
			'tipo_comision' => 'string',
			'monto' => 'required',
			'observaciones' => 'string',
			'documento' => 'string',
			'estado' => 'required|string',
        ];
    }
}
