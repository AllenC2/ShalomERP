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
    public function empleado()
    {
        return $this->belongsTo(\App\Models\Empleado::class, 'empleado_id', 'id');
    }
    
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
     * Calcula el saldo pendiente después de un pago específico (sin incluir ese pago en el cálculo)
     * 
     * @param float $montoPago
     * @param int|null $excluirPagoId ID del pago a excluir del cálculo
     * @return float
     */
    public function calcularSaldoDespuesDePago($montoPago, $excluirPagoId = null)
    {
        $query = $this->pagos()->where('estado', 'Hecho');
        
        if ($excluirPagoId) {
            $query->where('id', '!=', $excluirPagoId);
        }
        
        $totalPagado = $query->sum('monto');
        $nuevoSaldo = $this->monto_total - ($totalPagado + $montoPago);
        
        return max(0, $nuevoSaldo);
    }
    
    /**
     * Recalcula el saldo restante de todos los pagos del contrato
     * Útil cuando se elimina un pago o se hacen cambios masivos
     */
    public function recalcularSaldosPagos()
    {
        $pagos = $this->pagos()->where('estado', 'hecho')->orderBy('fecha_pago', 'asc')->get();
        $saldoAcumulado = 0;
        
        foreach ($pagos as $pago) {
            $saldoAcumulado += $pago->monto;
            $nuevoSaldo = max(0, $this->monto_total - $saldoAcumulado);
            $pago->update(['saldo_restante' => $nuevoSaldo]);
        }
    }

    /**
     * Obtiene las cuotas pendientes vencidas (considerando tolerancia)
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCuotasVencidasAttribute()
    {
        // Obtener la tolerancia de pagos configurada
        $toleranciaDias = \App\Models\Ajuste::obtenerToleranciaPagos();
        
        // Calcular la fecha límite considerando la tolerancia
        $fechaLimite = \Carbon\Carbon::now()->subDays($toleranciaDias)->endOfDay();
        
        return $this->pagos()
            ->where('estado', 'pendiente')
            ->where('tipo_pago', 'cuota')
            ->where('fecha_pago', '<', $fechaLimite)
            ->orderBy('fecha_pago', 'asc')
            ->get();
    }

    /**
     * Obtiene el número de cuotas vencidas
     * 
     * @return int
     */
    public function getNumCuotasVencidasAttribute()
    {
        return $this->cuotas_vencidas->count();
    }

    /**
     * Obtiene el monto total de cuotas vencidas
     * 
     * @return float
     */
    public function getMontoVencidoAttribute()
    {
        return $this->cuotas_vencidas->sum('monto');
    }

    /**
     * Obtiene los días de retraso de la cuota más antigua vencida (considerando tolerancia)
     * 
     * @return int|null
     */
    public function getDiasRetrasoAttribute()
    {
        $cuotaMasAntigua = $this->cuotas_vencidas->first();
        
        if (!$cuotaMasAntigua) {
            return null;
        }
        
        // Obtener la tolerancia configurada
        $toleranciaDias = \App\Models\Ajuste::obtenerToleranciaPagos();
        
        // Calcular días desde la fecha de pago más la tolerancia
        $fechaLimite = \Carbon\Carbon::parse($cuotaMasAntigua->fecha_pago)->addDays($toleranciaDias);
        
        // Solo contar días de retraso si ya pasó el período de tolerancia
        return max(0, \Carbon\Carbon::now()->diffInDays($fechaLimite, false));
    }

    /**
     * Obtiene las cuotas en período de tolerancia (vencidas pero dentro del margen permitido)
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCuotasEnToleranciaAttribute()
    {
        // Obtener la tolerancia de pagos configurada
        $toleranciaDias = \App\Models\Ajuste::obtenerToleranciaPagos();
        
        // Si no hay tolerancia, no hay cuotas en tolerancia
        if ($toleranciaDias == 0) {
            return collect();
        }
        
        // Fecha límite para tolerancia
        $fechaLimiteTolerancia = \Carbon\Carbon::now()->subDays($toleranciaDias)->endOfDay();
        
        return $this->pagos()
            ->where('estado', 'pendiente')
            ->where('tipo_pago', 'cuota')
            ->where('fecha_pago', '<', \Carbon\Carbon::now()->endOfDay()) // Vencidas
            ->where('fecha_pago', '>=', $fechaLimiteTolerancia) // Pero dentro de tolerancia
            ->orderBy('fecha_pago', 'asc')
            ->get();
    }

    /**
     * Obtiene el número de cuotas en período de tolerancia
     * 
     * @return int
     */
    public function getNumCuotasEnToleranciaAttribute()
    {
        return $this->cuotas_en_tolerancia->count();
    }

    /**
     * Obtiene el monto total de cuotas en período de tolerancia
     * 
     * @return float
     */
    public function getMontoEnToleranciaAttribute()
    {
        return $this->cuotas_en_tolerancia->sum('monto');
    }

    /**
     * Obtiene todas las cuotas pendientes (vencidas y por vencer)
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCuotasPendientesAttribute()
    {
        return $this->pagos()
            ->where('estado', 'pendiente')
            ->where('tipo_pago', 'cuota')
            ->orderBy('fecha_pago', 'asc')
            ->get();
    }

    /**
     * Obtiene el monto total pendiente de todas las cuotas
     * 
     * @return float
     */
    public function getMontoPendienteTotalAttribute()
    {
        return $this->cuotas_pendientes->sum('monto');
    }

    /**
     * Verifica si el contrato tiene cuotas vencidas
     * 
     * @return bool
     */
    public function getTieneCuotasVencidasAttribute()
    {
        return $this->num_cuotas_vencidas > 0;
    }

    /**
     * Obtiene la próxima cuota a vencer
     * 
     * @return \App\Models\Pago|null
     */
    public function getProximaCuotaAttribute()
    {
        return $this->pagos()
            ->where('estado', 'pendiente')
            ->where('tipo_pago', 'cuota')
            ->where('fecha_pago', '>=', now())
            ->orderBy('fecha_pago', 'asc')
            ->first();
    }

    /**
     * Obtiene información resumida del estado de pagos
     * 
     * @return array
     */
    public function getEstadoPagosAttribute()
    {
        $cuotasVencidas = $this->num_cuotas_vencidas;
        $montoVencido = $this->monto_vencido;
        $cuotasEnTolerancia = $this->num_cuotas_en_tolerancia;
        $montoEnTolerancia = $this->monto_en_tolerancia;
        $diasRetraso = $this->dias_retraso;
        $proximaCuota = $this->proxima_cuota;
        $tolerancia = \App\Models\Ajuste::obtenerToleranciaPagos();
        
        return [
            'cuotas_vencidas' => $cuotasVencidas,
            'monto_vencido' => $montoVencido,
            'cuotas_en_tolerancia' => $cuotasEnTolerancia,
            'monto_en_tolerancia' => $montoEnTolerancia,
            'dias_retraso' => $diasRetraso,
            'tiene_vencidas' => $cuotasVencidas > 0,
            'tiene_en_tolerancia' => $cuotasEnTolerancia > 0,
            'tolerancia_dias' => $tolerancia,
            'proxima_cuota' => $proximaCuota ? [
                'monto' => $proximaCuota->monto,
                'fecha' => $proximaCuota->fecha_pago,
                'dias_restantes' => \Carbon\Carbon::parse($proximaCuota->fecha_pago)->diffInDays(now(), false)
            ] : null,
            'monto_pendiente_total' => $this->monto_pendiente_total
        ];
    }
    
}
