<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RutaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if ($this->isMethod('post')) {
            return [
                'empleado_id' => 'required|string|exists:empleados,id',
                'fecha' => 'required|date',
                'fecha_limite' => 'required|date|after_or_equal:fecha',
                'notas' => 'nullable|string|max:1000',
                'contratos' => 'required|array|min:1',
                'contratos.*' => 'exists:contratos,id',
                'punto_casa' => 'nullable|in:inicio,final,ambos',
            ];
        }

        return [
            'fecha' => 'sometimes|date',
            'fecha_limite' => 'sometimes|date|after_or_equal:fecha',
            'estado' => 'sometimes|in:planeada,en_curso,completada,cancelada',
            'notas' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'empleado_id.required' => 'Debe seleccionar un empleado.',
            'empleado_id.exists' => 'El empleado seleccionado no existe.',
            'fecha.required' => 'La fecha es obligatoria.',
            'fecha_limite.required' => 'La fecha límite es obligatoria.',
            'fecha_limite.after_or_equal' => 'La fecha límite debe ser igual o posterior a la fecha de la ruta.',
            'contratos.required' => 'Debe seleccionar al menos un contrato.',
            'contratos.min' => 'Debe seleccionar al menos un contrato.',
        ];
    }
}
