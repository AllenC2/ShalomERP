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
 * @property Empleado $empleado
 * @property Paquete $paquete
 * @property Comisione[] $comisiones
 * @property Pago[] $pagos
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Contrato extends Model
{
    
    protected $perPage = 20;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['cliente_id', 'empleado_id', 'paquete_id', 'fecha_inicio', 'fecha_fin', 'monto_total', 'monto_inicial', 'plazo_tipo', 'plazo_cantidad', 'plazo_frencuencia', 'observaciones', 'documento', 'estado'];


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
        return $this->hasMany(\App\Models\Comisione::class, 'id', 'contrato_id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pagos()
    {
        return $this->hasMany(\App\Models\Pago::class, 'id', 'contrato_id');
    }
    
}
