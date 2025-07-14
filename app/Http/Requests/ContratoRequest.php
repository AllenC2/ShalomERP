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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
			'cliente_id' => 'required',
            'empleado_id' => 'nullable',
            'paquete_id' => 'nullable',
			'fecha_inicio' => 'required',
            'fecha_fin' => 'nullable|date',
            'monto_inicial' => 'nullable',
			'plazo_tipo' => 'string',
            'plazo_cantidad' => 'nullable|integer',
            'plazo_frencuencia' => 'nullable|integer',
			'observaciones' => 'string',
			'documento' => 'string',
			'estado' => 'required|string',
        ];
    }
}
