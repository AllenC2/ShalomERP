<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Cliente
 *
 * @property $id
 * @property $nombre
 * @property $apellido
 * @property $email
 * @property $telefono
 * @property $domicilio
 * @property $created_at
 * @property $updated_at
 *
 * @property Contrato[] $contratos
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Cliente extends Model
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
    public function contratos()
    {
        return $this->hasMany(\App\Models\Contrato::class, 'id', 'cliente_id');
    }
    
}
