<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Empleado
 *
 * @property $id
 * @property $created_at
 * @property $updated_at
 * @property $nombre
 * @property $apellido
 * @property $user_id
 * @property $telefono
 * @property $domicilio
 * @property $estado
 *
 * @property User $user
 * @property Comisione[] $comisiones
 * @property Contrato[] $contratos
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Empleado extends Model
{

    protected $perPage = 20;

    /**
     * Indica que la clave primaria no es auto-incremental
     */
    public $incrementing = false;

    /**
     * El tipo de la clave primaria
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['id', 'nombre', 'apellido', 'user_id', 'telefono', 'domicilio', 'estado'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comisiones()
    {
        return $this->hasMany(\App\Models\Comisione::class, 'empleado_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function contratos()
    {
        return $this->belongsToMany(\App\Models\Contrato::class, 'comisiones', 'empleado_id', 'contrato_id')
                    ->distinct();
    }
    
}
