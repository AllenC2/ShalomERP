<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Porcentaje
 *
 * @property $id
 * @property $paquete_id
 * @property $cantidad_porcentaje
 * @property $tipo_porcentaje
 * @property $observaciones
 * @property $created_at
 * @property $updated_at
 *
 * @property Paquete $paquete
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Porcentaje extends Model
{
    
    protected $perPage = 20;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['paquete_id', 'cantidad_porcentaje', 'tipo_porcentaje', 'observaciones'];


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
        return $this->hasMany(\App\Models\Comisione::class, 'tipo_comision', 'tipo_porcentaje');
    }
    
}
