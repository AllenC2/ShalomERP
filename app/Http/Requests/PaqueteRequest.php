<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaqueteRequest extends FormRequest
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
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'porcentajes' => 'nullable|array|min:1',
            'porcentajes.*.cantidad_porcentaje' => 'required_with:porcentajes|numeric|min:0|max:100',
            'porcentajes.*.tipo_porcentaje' => 'required_with:porcentajes|string|max:30',
            'porcentajes.*.observaciones' => 'nullable|string|max:255',
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
            'nombre.required' => 'El nombre del paquete es obligatorio.',
            'precio.required' => 'El precio del paquete es obligatorio.',
            'precio.numeric' => 'El precio debe ser un número válido.',
            'porcentajes.min' => 'Debe agregar al menos un porcentaje.',
            'porcentajes.*.cantidad_porcentaje.required_with' => 'La cantidad del porcentaje es obligatoria.',
            'porcentajes.*.cantidad_porcentaje.numeric' => 'La cantidad del porcentaje debe ser un número.',
            'porcentajes.*.cantidad_porcentaje.max' => 'La cantidad del porcentaje no puede ser mayor a 100.',
            'porcentajes.*.tipo_porcentaje.required_with' => 'El tipo de porcentaje es obligatorio.',
            'porcentajes.*.tipo_porcentaje.max' => 'El tipo de porcentaje no puede tener más de 30 caracteres.',
        ];
    }
}
