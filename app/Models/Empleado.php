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
 * @property $email
 * @property $telefono
 * @property $domicilio
 *
 * @property Comisione[] $comisiones
 * @property Contrato[] $contratos
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Empleado extends Model
{
    
    protected $perPage = 20;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['nombre', 'apellido', 'email', 'telefono', 'domicilio'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comisiones()
    {
        return $this->hasMany(\App\Models\Comisione::class, 'empleado_id', 'id');
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contratos()
    {
        return $this->hasMany(\App\Models\Contrato::class, 'id', 'empleado_id');
    }
    
}
