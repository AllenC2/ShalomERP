<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visita extends Model
{
    protected $fillable = [
        'contrato_id',
        'user_id',
        'comentarios',
        'ubicacion_evidencia'
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

    /**
     * Get latitude and longitude from spatial point.
     * Returns ['lat' => ..., 'lng' => ...] or null.
     */
    public function getCoordinatesAttribute()
    {
        $binary = $this->attributes['ubicacion_evidencia'] ?? null;
        if (!$binary) {
            return null;
        }

        // Check if it's already a string with POINT format (e.g. from raw query or SQLite)
        if (is_string($binary) && preg_match('/POINT\(([-\d\.]+)\s+([-\d\.]+)\)/i', $binary, $matches)) {
            return [
                'lat' => (double)$matches[2],
                'lng' => (double)$matches[1],
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
}
