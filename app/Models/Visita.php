<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visita extends Model
{
    protected $fillable = [
        'contrato_id',
        'user_id',
        'ruta_parada_id',
        'lote',
        'en_domicilio',
        'recibido',
        'receptor_nombre',
        'receptor_parentesco',
        'comentarios',
        'ubicacion_evidencia',
        'latitud',
        'longitud',
        'adeudo_momento',
    ];

    protected $casts = [
        'en_domicilio' => 'boolean',
        'recibido' => 'boolean',
        'adeudo_momento' => 'decimal:2',
        'latitud' => 'float',
        'longitud' => 'float',
    ];

    public const PARENTESCOS = [
        'titular' => 'Titular',
        'familiar' => 'Familiar',
        'vecino' => 'Vecino',
        'otro' => 'Otro',
    ];

    /**
     * Relación con el contrato
     */
    public function contrato()
    {
        return $this->belongsTo(Contrato::class);
    }

    /**
     * Relación con el empleado (usuario)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rutaParada()
    {
        return $this->belongsTo(RutaParada::class, 'ruta_parada_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'visita_id');
    }

    /**
     * Coordenadas del registro (GPS del cobrador al abonar / marcar visita).
     * Prefiere columnas latitud/longitud; si faltan, intenta leer ubicacion_evidencia.
     *
     * @return array{lat: float, lng: float}|null
     */
    public function getCoordinatesAttribute(): ?array
    {
        if ($this->latitud !== null && $this->longitud !== null) {
            return [
                'lat' => (float) $this->latitud,
                'lng' => (float) $this->longitud,
            ];
        }

        $binary = $this->attributes['ubicacion_evidencia'] ?? null;
        if (! $binary) {
            return null;
        }

        // Check if it's already a string with POINT format (e.g. from raw query or SQLite)
        if (is_string($binary) && preg_match('/POINT\(([-\d\.]+)\s+([-\d\.]+)\)/i', $binary, $matches)) {
            return [
                'lat' => (float) $matches[2],
                'lng' => (float) $matches[1],
            ];
        }

        // MySQL spatial format: 4 bytes SRID + 1 byte Byte Order + 4 bytes WKB Type + 8 bytes X (lng) + 8 bytes Y (lat)
        if (is_string($binary) && strlen($binary) === 25) {
            $data = @unpack('x9/dLng/dLat', $binary);
            if ($data && isset($data['Lat']) && isset($data['Lng'])) {
                return [
                    'lat' => $data['Lat'],
                    'lng' => $data['Lng'],
                ];
            }
        }

        return null;
    }

    public function tieneUbicacionRegistro(): bool
    {
        return $this->coordinates !== null;
    }
}
