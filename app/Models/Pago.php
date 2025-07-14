<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Pago
 *
 * @property $id
 * @property $contrato_id
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
    
    protected $perPage = 20;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['contrato_id', 'monto', 'observaciones', 'fecha_pago', 'metodo_pago', 'estado', 'documento'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contrato()
    {
        return $this->belongsTo(\App\Models\Contrato::class, 'contrato_id', 'id');
    }
    
}
