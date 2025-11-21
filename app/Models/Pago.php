<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Pago
 *
 * @property $id
 * @property $contrato_id
 * @property $tipo_pago
 * @property $monto
 * @property $observaciones
 * @property $fecha_pago
 * @property $metodo_pago
 * @property $estado
 * @property $documento
 * @property $created_at
 * @property $updated_at
 *
 * @property Contrato $contrato
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Pago extends Model
{
    // Constantes para valores permitidos
    const TIPOS_PAGO = [
        'cuota' => 'Cuota Regular',
        'parcialidad' => 'Parcialidad',
        'inicial' => 'Pago Inicial',
        'bonificación' => 'Bonificación'
    ];

    const METODOS_PAGO = [
        'efectivo' => 'Efectivo',
        'transferencia bancaria' => 'Transferencia Bancaria',
        'tarjeta credito/debito' => 'Tarjeta Crédito/Débito',
        'cheque' => 'Cheque',
        'otro' => 'Otro'
    ];

    const ESTADOS = [
        'pendiente' => 'Pendiente',
        'hecho' => 'Hecho',
        'retrasado' => 'Retrasado'
    ];
    
    protected $perPage = 20;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['contrato_id', 'pago_padre_id', 'tipo_pago', 'metodo_pago', 'monto', 'fecha_pago', 'numero_cuota', 'referencia', 'saldo_restante', 'documento', 'observaciones', 'estado'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_pago' => 'datetime',
        'monto' => 'decimal:2',
        'saldo_restante' => 'decimal:2'
    ];

    /**
     * Boot method to set up model events
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($model) {
            // Validar tipo_pago
            if (!array_key_exists($model->tipo_pago, self::TIPOS_PAGO)) {
                throw new \InvalidArgumentException('Tipo de pago no válido: ' . $model->tipo_pago);
            }
            
            // Validar metodo_pago
            if (!array_key_exists($model->metodo_pago, self::METODOS_PAGO)) {
                throw new \InvalidArgumentException('Método de pago no válido: ' . $model->metodo_pago);
            }
            
            // Validar estado
            if (!array_key_exists($model->estado, self::ESTADOS)) {
                throw new \InvalidArgumentException('Estado no válido: ' . $model->estado);
            }
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contrato()
    {
        return $this->belongsTo(\App\Models\Contrato::class, 'contrato_id', 'id');
    }

    /**
     * Relación con el pago padre (para parcialidades)
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pagoPadre()
    {
        return $this->belongsTo(Pago::class, 'pago_padre_id', 'id');
    }

    /**
     * Relación con los pagos hijos (parcialidades)
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function parcialidades()
    {
        return $this->hasMany(Pago::class, 'pago_padre_id', 'id')
                    ->where('tipo_pago', 'parcialidad');
    }

    /**
     * Obtener el monto total de las parcialidades aplicadas a este pago
     * @return float
     */
    public function getMontoParcialidadesAttribute()
    {
        return $this->parcialidades()
                    ->where('estado', 'hecho')
                    ->sum('monto');
    }

    /**
     * Obtener el monto pendiente de la cuota (monto original - parcialidades pagadas)
     * @return float
     */
    public function getMontoPendienteAttribute()
    {
        if ($this->tipo_pago !== 'cuota') {
            return $this->monto;
        }
        
        // Si la cuota está completada (estado = 'hecho'), el monto pendiente es 0
        if ($this->estado === 'hecho') {
            return 0;
        }
        
        return max(0, $this->monto - $this->getMontoParcialidadesAttribute());
    }

    /**
     * Obtener el monto original de la cuota (antes de aplicar parcialidades)
     * @return float
     */
    public function getMontoOriginalCuotaAttribute()
    {
        if ($this->tipo_pago !== 'cuota') {
            return $this->monto;
        }
        
        // Si la cuota está pendiente, el monto actual ES el monto original
        // (ya que ahora no modificamos el monto cuando aplicamos parcialidades)
        if ($this->estado === 'pendiente') {
            return $this->monto;
        }
        
        // Si está completada por parcialidades, el monto original es la suma de todas las parcialidades
        $totalParcialidades = $this->getMontoParcialidadesAttribute();
        if ($totalParcialidades > 0) {
            return $totalParcialidades;
        }
        
        // Si no hay parcialidades pero está completada, el monto actual es el original
        return $this->monto;
    }

    /**
     * Obtener el label del tipo de pago
     */
    public function getTipoPagoLabelAttribute()
    {
        return self::TIPOS_PAGO[$this->tipo_pago] ?? $this->tipo_pago;
    }

    /**
     * Obtener el label del método de pago
     */
    public function getMetodoPagoLabelAttribute()
    {
        return self::METODOS_PAGO[$this->metodo_pago] ?? $this->metodo_pago;
    }

    /**
     * Obtener el label del estado
     */
    public function getEstadoLabelAttribute()
    {
        return self::ESTADOS[$this->estado] ?? $this->estado;
    }
    
}
