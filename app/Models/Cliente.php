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
 * @property $calle_y_numero
 * @property $cruces
 * @property $colonia
 * @property $municipio
 * @property $estado
 * @property $codigo_postal
 * @property $domicilio_completo
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
    protected $fillable = [
        'nombre', 
        'apellido', 
        'email', 
        'telefono', 
        'calle_y_numero',
        'cruces',
        'colonia',
        'municipio',
        'estado',
        'codigo_postal',
        'domicilio_completo'
    ];

    /**
     * Mutator para crear el domicilio completo automáticamente
     */
    public function setDomicilioCompletoAttribute($value)
    {
        if (!$value) {
            // Si no viene valor, lo construimos automáticamente
            $partes = array_filter([
                $this->calle_y_numero,
                $this->cruces ? "Entre: {$this->cruces}" : null,
                $this->colonia,
                $this->municipio,
                $this->estado,
                $this->codigo_postal ? "CP: {$this->codigo_postal}" : null
            ]);
            
            $this->attributes['domicilio_completo'] = implode(', ', $partes);
        } else {
            $this->attributes['domicilio_completo'] = $value;
        }
    }

    /**
     * Actualizar domicilio completo cuando se modifiquen los campos individuales
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($cliente) {
            if (!$cliente->domicilio_completo || $cliente->isDirty(['calle_y_numero', 'cruces', 'colonia', 'municipio', 'estado', 'codigo_postal'])) {
                $partes = array_filter([
                    $cliente->calle_y_numero,
                    $cliente->cruces ? "Entre: {$cliente->cruces}" : null,
                    $cliente->colonia,
                    $cliente->municipio,
                    $cliente->estado,
                    $cliente->codigo_postal ? "CP: {$cliente->codigo_postal}" : null
                ]);
                
                $cliente->domicilio_completo = implode(', ', $partes);
            }
        });
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contratos()
    {
        return $this->hasMany(\App\Models\Contrato::class, 'cliente_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contratosActivos()
    {
        return $this->hasMany(\App\Models\Contrato::class, 'cliente_id', 'id')
                    ->where('estado', \App\Models\Contrato::ESTADO_ACTIVO);
    }
    
}
