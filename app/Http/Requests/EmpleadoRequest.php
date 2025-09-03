<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmpleadoRequest extends FormRequest
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
        $rules = [
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'domicilio' => 'nullable|string|max:500',
        ];

        // Solo requerir contraseña al crear (no al editar)
        if ($this->isMethod('post')) {
            $rules['email'] = 'required|email|unique:users,email';
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        // Al editar, permitir email duplicado si es el mismo usuario
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $empleado = $this->route('empleado');
            if ($empleado && $empleado->user_id) {
                $rules['email'] = 'required|email|unique:users,email,' . $empleado->user_id;
            } else {
                $rules['email'] = 'required|email|unique:users,email';
            }
        }

        return $rules;
    }
}
