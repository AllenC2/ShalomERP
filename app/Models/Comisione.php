<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Comisione
 *
 * @property $id
 * @property $contrato_id
 * @property $empleado_id
 * @property $fecha_comision
 * @property $tipo_comision
 * @property $monto
 * @property $observaciones
 * @property $documento
 * @property $estado
 * @property $created_at
 * @property $updated_at
 *
 * @property Contrato $contrato
 * @property Empleado $empleado
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Comisione extends Model
{
    
    protected $perPage = 20;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['contrato_id', 'empleado_id', 'comision_padre_id', 'fecha_comision', 'nombre_paquete', 'porcentaje', 'tipo_comision', 'monto', 'observaciones', 'documento', 'estado'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_comision' => 'datetime',
    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contrato()
    {
        return $this->belongsTo(\App\Models\Contrato::class, 'contrato_id', 'id');
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
    public function comisionPadre()
    {
        return $this->belongsTo(\App\Models\Comisione::class, 'comision_padre_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function parcialidades()
    {
        return $this->hasMany(\App\Models\Comisione::class, 'comision_padre_id', 'id');
    }
    
}
