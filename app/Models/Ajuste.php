<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ajuste extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'valor',
        'tipo',
        'descripcion',
        'activo'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obtener el valor de un ajuste por su nombre
     *
     * @param string $nombre
     * @param mixed $default
     * @return mixed
     */
    public static function obtener($nombre, $default = null)
    {
        $ajuste = self::where('nombre', $nombre)
                     ->where('activo', true)
                     ->first();

        if (!$ajuste) {
            return $default;
        }

        // Convertir el valor según el tipo
        return match($ajuste->tipo) {
            'boolean' => filter_var($ajuste->valor, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $ajuste->valor,
            'float' => (float) $ajuste->valor,
            'array' => json_decode($ajuste->valor, true),
            'json' => json_decode($ajuste->valor, true),
            default => $ajuste->valor
        };
    }

    /**
     * Establecer el valor de un ajuste
     *
     * @param string $nombre
     * @param mixed $valor
     * @param string $tipo
     * @param string $descripcion
     * @return self
     */
    public static function establecer($nombre, $valor, $tipo = 'string', $descripcion = null)
    {
        // Convertir el valor a string para almacenamiento
        if (in_array($tipo, ['array', 'json'])) {
            $valor = json_encode($valor);
        }

        return self::updateOrCreate(
            ['nombre' => $nombre],
            [
                'valor' => $valor,
                'tipo' => $tipo,
                'descripcion' => $descripcion,
                'activo' => true
            ]
        );
    }

    /**
     * Obtener toda la información de la empresa
     *
     * @return array
     */
    public static function obtenerInfoEmpresa()
    {
        $campos = [
            'razon_social' => 'empresa_razon_social',
            'rfc' => 'empresa_rfc',
            'calle_numero' => 'empresa_calle_numero',
            'colonia' => 'empresa_colonia',
            'ciudad' => 'empresa_ciudad',
            'estado' => 'empresa_estado',
            'pais' => 'empresa_pais',
            'codigo_postal' => 'empresa_codigo_postal',
            'telefono' => 'empresa_telefono',
            'email' => 'empresa_email',
        ];

        // Valores por defecto
        $valoresDefault = [
            'razon_social' => 'RazonSocial',
            'rfc' => 'RFC0000000',
            'calle_numero' => 'Av. Principal #123',
            'colonia' => 'Col. COLONIA',
            'ciudad' => 'Guadalajara',
            'estado' => 'Jalisco',
            'pais' => 'México',
            'codigo_postal' => '00000',
            'telefono' => '(000) 123-4567',
            'email' => 'contacto@funerariashalom.com',
        ];

        $resultado = [];
        foreach ($campos as $key => $nombre) {
            $ajuste = self::where('nombre', $nombre)
                         ->where('activo', true)
                         ->first();
            
            $valor = $ajuste ? $ajuste->valor : '';
            
            // Si el valor está vacío, usar el valor por defecto
            $resultado[$key] = !empty($valor) ? $valor : $valoresDefault[$key];
        }

        return $resultado;
    }

    /**
     * Obtener tolerancia de pagos en días
     *
     * @return int
     */
    public static function obtenerToleranciaPagos()
    {
        $ajuste = self::where('nombre', 'tolerancia_pagos_dias')
                     ->where('activo', true)
                     ->first();

        return $ajuste ? (int)$ajuste->valor : 0;
    }
}