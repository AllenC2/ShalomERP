<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Paquete
 *
 * @property $id
 * @property $nombre
 * @property $descripcion
 * @property $precio
 * @property $created_at
 * @property $updated_at
 *
 * @property Contrato[] $contratos
 * @property Porcentaje[] $porcentajes
 * @package App
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Paquete extends Model
{
    
    protected $perPage = 20;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['nombre', 'descripcion', 'precio'];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contratos()
    {
        return $this->hasMany(\App\Models\Contrato::class, 'paquete_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function porcentajes()
    {
        return $this->hasMany(\App\Models\Porcentaje::class, 'paquete_id', 'id');
    }
    
}
