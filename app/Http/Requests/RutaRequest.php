<?php

namespace App\Http\Requests;

use App\Models\RutaPlantilla;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
                'nombre' => 'required|string|max:120',
                'empleado_id' => 'required|string|exists:empleados,id',
                'fecha' => 'required|date',
                'frecuencia' => ['required', Rule::in([
                    RutaPlantilla::FRECUENCIA_DIARIA,
                    RutaPlantilla::FRECUENCIA_SEMANAL,
                    RutaPlantilla::FRECUENCIA_MENSUAL,
                ])],
                'notas' => 'nullable|string|max:1000',
                'contratos' => 'required|array|min:1',
                'contratos.*' => 'exists:contratos,id',
                'punto_casa' => 'nullable|in:inicio,final,ambos',
            ];
        }

        return [
            'nombre' => 'sometimes|string|max:120',
            'empleado_id' => 'sometimes|string|exists:empleados,id',
            'fecha' => 'sometimes|date',
            'notas' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la ruta es obligatorio.',
            'empleado_id.required' => 'Debe seleccionar un empleado.',
            'empleado_id.exists' => 'El empleado seleccionado no existe.',
            'fecha.required' => 'La fecha es obligatoria.',
            'frecuencia.required' => 'Debe elegir la frecuencia de la ruta.',
            'frecuencia.in' => 'La frecuencia no es válida.',
            'contratos.required' => 'Debe agregar al menos un contrato.',
            'contratos.min' => 'Debe agregar al menos un contrato.',
        ];
    }
}
