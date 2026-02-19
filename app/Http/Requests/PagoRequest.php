<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Pago;

class PagoRequest extends FormRequest
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
        $metodosPagoKeys = implode(',', array_keys(Pago::METODOS_PAGO));
        $estadosKeys = implode(',', array_keys(Pago::ESTADOS));

        return [
            'contrato_id' => 'nullable|exists:contratos,id',
            'monto' => 'required|numeric|min:0.01',
            'observaciones' => 'nullable|string|max:500',
            'fecha_pago' => 'required|date_format:Y-m-d\TH:i',
            'metodo_pago' => 'required|string|in:' . $metodosPagoKeys,
            'estado' => 'required|string|in:' . $estadosKeys,
            'documento' => 'nullable|file|mimes:pdf,jpeg,jpg,png,webp,doc,docx,xls,xlsx|max:10240', // máximo 10MB
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'contrato_id.exists' => 'El contrato seleccionado no existe.',
            'monto.required' => 'El monto es obligatorio.',
            'monto.numeric' => 'El monto debe ser un número válido.',
            'monto.min' => 'El monto debe ser mayor a cero.',
            'observaciones.max' => 'Las observaciones no pueden exceder 500 caracteres.',
            'fecha_pago.required' => 'La fecha y hora del pago es obligatoria.',
            'fecha_pago.date_format' => 'La fecha y hora del pago debe tener el formato correcto.',
            'metodo_pago.required' => 'El método de pago es obligatorio.',
            'metodo_pago.in' => 'El método de pago debe ser: ' . implode(', ', array_values(Pago::METODOS_PAGO)),
            'estado.required' => 'El estado del pago es obligatorio.',
            'estado.in' => 'El estado debe ser: ' . implode(', ', array_values(Pago::ESTADOS)),
            'documento.file' => 'El documento debe ser un archivo válido.',
            'documento.mimes' => 'El documento debe ser un archivo PDF, Imagen, Word o Excel.',
            'documento.max' => 'El documento no puede ser mayor a 10MB.',
        ];
    }
}
