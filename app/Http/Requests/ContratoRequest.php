<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContratoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Limpiar campos de moneda antes de la validación
        $cleanedData = [];
        
        if ($this->has('monto_inicial')) {
            $cleanedData['monto_inicial'] = $this->cleanMoneyField($this->input('monto_inicial'));
        }
        
        if ($this->has('monto_bonificacion')) {
            $cleanedData['monto_bonificacion'] = $this->cleanMoneyField($this->input('monto_bonificacion'));
        }
        
        if ($this->has('monto_cuota')) {
            $cleanedData['monto_cuota'] = $this->cleanMoneyField($this->input('monto_cuota'));
        }
        
        if (!empty($cleanedData)) {
            $this->merge($cleanedData);
        }
    }
    
    /**
     * Clean money field by removing currency symbols and formatting
     */
    private function cleanMoneyField($value)
    {
        if (empty($value)) {
            return null;
        }
        
        // Remover símbolo de dólar, comas y espacios
        $cleaned = str_replace(['$', ',', ' '], '', $value);
        
        // Si queda vacío después de limpiar, retornar null
        if (empty($cleaned)) {
            return null;
        }
        
        // Verificar que sea un número válido
        if (is_numeric($cleaned)) {
            return $cleaned;
        }
        
        return $value; // Retornar original si no se puede limpiar
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
			'cliente_id' => 'required|exists:clientes,id',
            'paquete_id' => 'required|exists:paquetes,id',
			'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'monto_inicial' => 'nullable|numeric|min:0',
            'monto_bonificacion' => 'nullable|numeric|min:0',
            'monto_cuota' => 'nullable|numeric|min:0',
            'numero_cuotas' => 'required|integer|min:1',
            'frecuencia_cuotas' => 'required|integer|min:1',
			'observaciones' => 'nullable|string',
			'documento' => 'nullable|file|mimes:pdf|max:10240', // Máximo 10MB
			'estado' => 'nullable|in:activo,cancelado,finalizado,suspendido',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'cliente_id.required' => 'Debe seleccionar un cliente.',
            'cliente_id.exists' => 'El cliente seleccionado no es válido.',
            'paquete_id.required' => 'Debe seleccionar un paquete.',
            'paquete_id.exists' => 'El paquete seleccionado no es válido.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',
            'numero_cuotas.required' => 'El número de cuotas es obligatorio.',
            'numero_cuotas.integer' => 'El número de cuotas debe ser un número entero.',
            'numero_cuotas.min' => 'Debe tener al menos 1 cuota.',
            'frecuencia_cuotas.required' => 'La frecuencia de cuotas es obligatoria.',
            'frecuencia_cuotas.integer' => 'La frecuencia debe ser un número entero.',
            'frecuencia_cuotas.min' => 'La frecuencia debe ser de al menos 1 día.',
            'monto_inicial.numeric' => 'El monto inicial debe ser un número válido.',
            'monto_inicial.min' => 'El monto inicial debe ser mayor o igual a 0.',
            'monto_bonificacion.numeric' => 'La bonificación debe ser un número válido.',
            'monto_bonificacion.min' => 'La bonificación debe ser mayor o igual a 0.',
            'fecha_fin.date' => 'La fecha de fin debe ser una fecha válida.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio.',
            'documento.file' => 'El documento debe ser un archivo válido.',
            'documento.mimes' => 'El documento debe ser un archivo PDF.',
            'documento.max' => 'El documento no puede ser mayor a 10MB.',
            'estado.in' => 'El estado seleccionado no es válido.',
        ];
    }
}
