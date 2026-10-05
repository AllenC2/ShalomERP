<?php

namespace App\Http\Requests;

use App\Models\Contrato;
use App\Models\Pago;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->hasAny(['monto', 'contrato_id', 'estado'])) {
                return;
            }

            $contratoId = $this->input('contrato_id');
            if (!$contratoId) {
                return;
            }

            $contrato = Contrato::with('pagos')->find($contratoId);
            if (!$contrato) {
                return;
            }

            $pagoActual = $this->route('pago');
            $pagoActual = $pagoActual instanceof Pago ? $pagoActual : null;

            $pagosBase = collect($contrato->pagos->all());
            if ($pagoActual) {
                $pagosBase = $pagosBase
                    ->reject(fn ($pago) => (int) $pago->id === (int) $pagoActual->id)
                    ->values();
            }

            $simulado = new Pago([
                'contrato_id' => $contrato->id,
                'tipo_pago' => $this->input('tipo_pago') ?: ($pagoActual?->tipo_pago ?? 'cuota'),
                'monto' => $this->input('monto'),
                'estado' => $this->input('estado') ?: ($pagoActual?->estado ?? 'hecho'),
            ]);
            $simulado->id = $pagoActual?->id ?? 0;
            $simulado->pago_padre_id = $pagoActual?->pago_padre_id;

            $pagadoSinEste = round((float) calcularMontoPagadoContrato($pagosBase), 2);
            $pagadoActual = round((float) calcularMontoPagadoContrato($contrato->pagos), 2);
            $pagadoNuevo = round((float) calcularMontoPagadoContrato($pagosBase->concat([$simulado])), 2);
            $limite = round((float) $contrato->monto_total, 2);

            if ($pagadoNuevo <= $limite || $pagadoNuevo <= $pagadoActual) {
                return;
            }

            $saldoDisponible = max(0, round($limite - $pagadoSinEste, 2));

            $validator->errors()->add(
                'monto',
                'Este pago haría que lo cobrado ($' . number_format($pagadoNuevo, 2) . ') supere el total del contrato ($' . number_format($limite, 2) . '). '
                . 'El saldo disponible es $' . number_format($saldoDisponible, 2) . '.'
            );
        });
    }
}
