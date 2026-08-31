<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RutaParada extends Model
{
    protected $fillable = [
        'ruta_id',
        'orden',
        'contrato_id',
        'cliente_id',
        'direccion_destino',
        'latitud',
        'longitud',
        'estado',
        'notas',
    ];

    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_VISITADA = 'visitada';
    const ESTADO_OMITIDA = 'omitida';

    public static function getEstadosValidos()
    {
        return [
            self::ESTADO_PENDIENTE => 'Pendiente',
            self::ESTADO_VISITADA => 'Visitada',
            self::ESTADO_OMITIDA => 'Omitida',
        ];
    }

    protected $casts = [
        'latitud' => 'float',
        'longitud' => 'float',
    ];

    public function ruta()
    {
        return $this->belongsTo(Ruta::class, 'ruta_id');
    }

    public function contrato()
    {
        return $this->belongsTo(Contrato::class, 'contrato_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function visitas()
    {
        return $this->hasMany(Visita::class, 'ruta_parada_id');
    }

    public function getEstadoBadgeAttribute()
    {
        return match ($this->estado) {
            self::ESTADO_PENDIENTE => 'bg-secondary',
            self::ESTADO_VISITADA => 'bg-success',
            self::ESTADO_OMITIDA => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public function getEstadoLabelAttribute()
    {
        return self::getEstadosValidos()[$this->estado] ?? $this->estado;
    }

    public function getTieneCoordenadasAttribute()
    {
        return $this->latitud !== null && $this->longitud !== null;
    }
}
