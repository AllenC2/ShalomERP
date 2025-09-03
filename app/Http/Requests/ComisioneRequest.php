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
            'nombre_paquete' => 'nullable|string',
            'porcentaje' => 'nullable|numeric|min:0|max:100',
			'tipo_comision' => 'nullable|string',
			'monto' => 'required',
			'observaciones' => 'nullable|string',
			'documento' => 'nullable|file|mimes:pdf|max:10240', // Máximo 10MB, solo PDF
			'estado' => 'required|string',
        ];
    }

    /**
     * Preparar los datos para la validación
     */
    public function prepareForValidation()
    {
        // Limpiar el formato del monto
        if ($this->has('monto')) {
            $this->merge([
                'monto' => str_replace(['$', ','], '', $this->input('monto'))
            ]);
        }
    }

    /**
     * Mensajes de error personalizados
     */
    public function messages(): array
    {
        return [
            'contrato_id.required' => 'El contrato es obligatorio.',
            'empleado_id.required' => 'El empleado es obligatorio.',
            'fecha_comision.date' => 'La fecha de comisión debe ser una fecha válida.',
            'porcentaje.numeric' => 'El porcentaje debe ser un número.',
            'porcentaje.min' => 'El porcentaje no puede ser menor a 0.',
            'porcentaje.max' => 'El porcentaje no puede ser mayor a 100.',
            'tipo_comision.required' => 'El tipo de comisión es obligatorio.',
            'monto.required' => 'El monto es obligatorio.',
            'documento.file' => 'El documento debe ser un archivo válido.',
            'documento.mimes' => 'El documento debe ser un archivo PDF.',
            'documento.max' => 'El documento no puede ser mayor a 10MB.',
            'estado.required' => 'El estado es obligatorio.',
        ];
    }
}
