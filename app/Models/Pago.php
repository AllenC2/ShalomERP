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
        'cuota' => 'Cuota',
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
        'pendiente' => 'Pendiente', // Keep for now as per instructions, though mostly 'hecho' will be used
        'hecho' => 'Hecho',
        'retrasado' => 'Retrasado'
    ];

    protected $perPage = 20;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['contrato_id', 'tipo_pago', 'metodo_pago', 'monto', 'fecha_pago', 'referencia', 'documento', 'observaciones', 'estado'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_pago' => 'datetime',
        'monto' => 'decimal:2',
    ];

    /**
     * Boot method to set up model events
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
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
