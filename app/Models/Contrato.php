<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Contrato
 *
 * @property $id
 * @property $cliente_id
 * @property $empleado_id
 * @property $paquete_id
 * @property $fecha_inicio
 * @property $fecha_fin
 * @property $monto_total
 * @property $monto_inicial
 * @property $plazo_tipo
 * @property $plazo_cantidad
 * @property $plazo_frencuencia
 * @property $observaciones
 * @property $documento
 * @property $estado
 * @property $created_at
 * @property $updated_at
 *
 * @property Cliente $cliente
 * @property Paquete $paquete
 * @property Comisione[] $comisiones
 * @property Pago[] $pagos
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Contrato extends Model
{

    protected $perPage = 20;

    // Constantes para los estados válidos
    const ESTADO_ACTIVO = 'activo';
    const ESTADO_CANCELADO = 'cancelado';
    const ESTADO_FINALIZADO = 'finalizado';
    const ESTADO_SUSPENDIDO = 'suspendido';

    // Obtener todos los estados válidos
    public static function getEstadosValidos()
    {
        return [
            self::ESTADO_ACTIVO => 'Activo',
            self::ESTADO_CANCELADO => 'Cancelado',
            self::ESTADO_FINALIZADO => 'Finalizado',
            self::ESTADO_SUSPENDIDO => 'Suspendido'
        ];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['cliente_id', 'paquete_id', 'fecha_inicio', 'fecha_fin', 'monto_total', 'monto_inicial', 'monto_bonificacion', 'numero_cuotas', 'frecuencia_cuotas', 'monto_cuota', 'observaciones', 'documento', 'estado'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cliente()
    {
        return $this->belongsTo(\App\Models\Cliente::class, 'cliente_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    // public function empleado() { ... } // Removed in refactor

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function paquete()
    {
        return $this->belongsTo(\App\Models\Paquete::class, 'paquete_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comisiones()
    {
        return $this->hasMany(\App\Models\Comisione::class, 'contrato_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pagos()
    {
        return $this->hasMany(\App\Models\Pago::class, 'contrato_id', 'id');
    }

    /**
     * Calcula el total pagado del contrato
     * 
     * @return float
     */
    public function getTotalPagadoAttribute()
    {
        return $this->pagos()->where('estado', 'hecho')->sum('monto');
    }

    /**
     * Calcula el saldo pendiente del contrato
     * 
     * @return float
     */
    public function getSaldoPendienteAttribute()
    {
        return max(0, $this->monto_total - $this->total_pagado);
    }

    /**
     * Obtiene información resumida del estado de cuenta
     * Reemplaza a getEstadoPagosAttribute
     * 
     * @return array
     */
    public function getEstadoCuentaAttribute()
    {
        return [
            'total' => $this->monto_total,
            'pagado' => $this->total_pagado,
            'pendiente' => $this->saldo_pendiente,
            'porcentaje' => $this->monto_total > 0 ? round(($this->total_pagado / $this->monto_total) * 100, 2) : 0
        ];
    }

    /**
     * Obtiene el estado detallado de pagos para la vista show
     * 
     * @return array
     */
    /**
     * Obtiene el estado detallado de pagos para la vista show
     * 
     * @return array
     */
    public function getEstadoPagosAttribute()
    {
        $pagosPendientes = $this->pagos()->where('estado', 'pendiente')->where('tipo_pago', 'cuota')->get();

        $cuotasVencidas = 0;
        $montoVencido = 0;
        $maxDiasRetraso = 0;

        $cuotasEnTolerancia = 0;

        foreach ($pagosPendientes as $pago) {
            if (pagoEstaRetrasado($pago->fecha_pago, $pago->estado)) {
                $cuotasVencidas++;
                // Usar monto_pendiente si existe, si no usar monto total
                $monto = $pago->monto_pendiente ?? $pago->monto;
                $montoVencido += $monto;

                $dias = diasDeRetraso($pago->fecha_pago, $pago->estado);
                if ($dias > $maxDiasRetraso) {
                    $maxDiasRetraso = $dias;
                }
            } else {
                // Verificar si está en tolerancia
                $fechaPago = \Carbon\Carbon::parse($pago->fecha_pago);
                $enTolerancia = $fechaPago->isPast(); // Si ya pasó la fecha pero no está "retrasado" (según función helper), está en tolerancia

                if ($enTolerancia) {
                    $cuotasEnTolerancia++;
                }
            }
        }

        return [
            'tiene_vencidas' => $cuotasVencidas > 0,
            'cuotas_vencidas' => $cuotasVencidas,
            'monto_vencido' => $montoVencido,
            'dias_retraso' => $maxDiasRetraso,
            'tiene_en_tolerancia' => $cuotasEnTolerancia > 0,
            'cuotas_en_tolerancia' => $cuotasEnTolerancia,
            'tolerancia_dias' => toleranciaPagos()
        ];
    }

    /**
     * Calcula la siguiente cuota a pagar basada en el progreso del contrato
     * 
     * @return object|null Retorna un objeto similar a Pago con los datos calculados o null si el contrato está finalizado
     */
    /**
     * Calcula el monto real de la cuota (usando config o promedio)
     */
    public function getMontoCuotaRealAttribute()
    {
        if ($this->monto_cuota > 0) {
            return $this->monto_cuota;
        }

        $montoInicial = $this->monto_inicial ?? 0;
        $montoBonificacion = $this->monto_bonificacion ?? 0;
        $montoFinanciado = max(0, $this->monto_total - $montoInicial - $montoBonificacion);

        return $this->numero_cuotas > 0 ? $montoFinanciado / $this->numero_cuotas : 0;
    }

    /**
     * Calcula cuántas cuotas se han pagado en total (con decimales)
     */
    public function getCuotasPagadasDecimalAttribute()
    {
        $montoCuota = $this->monto_cuota_real;

        if ($montoCuota <= 0)
            return 0;

        $totalPagado = $this->pagos()->where('estado', 'hecho')->sum('monto');

        return $totalPagado / $montoCuota;
    }

    /**
     * Calcula la siguiente cuota a pagar basada en el progreso del contrato
     * 
     * @return object|null Retorna un objeto similar a Pago con los datos calculados o null si el contrato está finalizado
     */
    public function getSiguientePagoCalculadoAttribute()
    {
        // 1. Obtener datos base
        $montoCuota = $this->monto_cuota_real;

        if ($montoCuota <= 0) {
            return null; // Evitar división por cero
        }

        // 2. Calcular cuotas pagadas (incluyendo fracciones)
        $cuotasPagadasDecimal = $this->cuotas_pagadas_decimal;
        $totalPagado = $this->pagos()->where('estado', 'hecho')->sum('monto');

        $montoInicial = $this->monto_inicial ?? 0;
        $montoBonificacion = $this->monto_bonificacion ?? 0;
        $montoFinanciado = max(0, $this->monto_total - $montoInicial - $montoBonificacion);

        // 3. Calcular la fecha del próximo pago
        // Fórmula: Fecha Inicio + (Cuotas Pagadas * Frecuencia)
        $fechaInicio = \Carbon\Carbon::parse($this->fecha_inicio);
        $diasPagados = $cuotasPagadasDecimal * $this->frecuencia_cuotas;
        $fechaProgramada = $fechaInicio->copy()->addDays($diasPagados); // Aquí sumamos los días exactos calculados

        // Determinar el número de la siguiente cuota (entero)
        $siguienteNumeroCuota = floor($cuotasPagadasDecimal) + 1;

        // Si ya se cubrió todo el monto financiado, no hay siguiente
        if ($totalPagado >= $montoFinanciado || $siguienteNumeroCuota > $this->numero_cuotas + 1) { // +1 por margen de error en decimales
            // Validar si realmente saldo pendiente es 0 o muy bajo
            if ($this->saldo_pendiente < 1) {
                return null;
            }
        }

        // Ajustar el número de cuota para visualización (tope en total de cuotas)
        $numeroCuotaVisual = min($siguienteNumeroCuota, $this->numero_cuotas);

        // 4. Calcular el monto pendiente para COMPLETAR la siguiente cuota entera
        // Cuanto falta para llegar al siguiente entero: (Siguiente entero - Actual decimal) * Monto Cuota
        $fraccionPendiente = $siguienteNumeroCuota - $cuotasPagadasDecimal;
        $montoPendienteParaSiguiente = $fraccionPendiente * $montoCuota;

        // Asegurar que no exceda el saldo total pendiente real del contrato
        $montoPendienteParaSiguiente = min($montoPendienteParaSiguiente, $this->saldo_pendiente);

        // 5. Crear objeto simulado
        $pagoSimulado = new \App\Models\Pago();
        $pagoSimulado->id = null; // Sin ID
        $pagoSimulado->contrato_id = $this->id;
        $pagoSimulado->tipo_pago = 'cuota';
        $pagoSimulado->numero_cuota = $numeroCuotaVisual;
        $pagoSimulado->monto = $montoCuota; // El monto "ideal" de la cuota
        $pagoSimulado->monto_pendiente = $montoPendienteParaSiguiente; // Lo que falta para estar "al día" con esa cuota
        $pagoSimulado->fecha_pago = $fechaProgramada;
        $pagoSimulado->estado = 'pendiente';

        return $pagoSimulado;
    }

    /**
     * Actualiza el campo proxima_fecha_pago en la base de datos evaluando
     * el estado actual del contrato.
     */
    public function actualizarProximaFechaPago()
    {
        $siguiente = $this->siguiente_pago_calculado;
        if ($siguiente) {
            $this->proxima_fecha_pago = $siguiente->fecha_pago;
        } else {
            $this->proxima_fecha_pago = null;
        }
        $this->saveQuietly();
    }
}
