<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class RutaActividad extends Model
{
    public const TIPO_ABONO = 'abono';

    public const TIPO_AUSENTE = 'ausente';

    public const TIPO_DESHACER = 'deshacer';

    public const TIPO_OMITIR = 'omitir';

    protected $table = 'ruta_actividades';

    protected $fillable = [
        'empleado_id',
        'user_id',
        'ruta_id',
        'ruta_parada_id',
        'visita_id',
        'tipo',
        'titulo',
        'detalle',
        'monto_total',
        'meta',
    ];

    protected $casts = [
        'monto_total' => 'decimal:2',
        'meta' => 'array',
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ruta(): BelongsTo
    {
        return $this->belongsTo(Ruta::class, 'ruta_id');
    }

    public static function registrar(array $datos): ?self
    {
        try {
            if (empty($datos['empleado_id'])) {
                $empleadoId = Auth::user()?->empleado?->id;
                if (! $empleadoId && ! empty($datos['ruta_id'])) {
                    $empleadoId = Ruta::query()->whereKey($datos['ruta_id'])->value('empleado_id');
                }
                $datos['empleado_id'] = $empleadoId;
            }

            if (empty($datos['empleado_id'])) {
                return null;
            }

            $datos['user_id'] = $datos['user_id'] ?? Auth::id();

            return static::create($datos);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('No se pudo registrar actividad de ruta: '.$e->getMessage());

            return null;
        }
    }

    public function etiquetaTipo(): string
    {
        return match ($this->tipo) {
            self::TIPO_ABONO => 'Abono',
            self::TIPO_AUSENTE => 'Ausente',
            self::TIPO_DESHACER => 'Deshacer visita',
            self::TIPO_OMITIR => 'Omitir parada',
            default => ucfirst((string) $this->tipo),
        };
    }

    public function badgeClass(): string
    {
        return match ($this->tipo) {
            self::TIPO_ABONO => 'bg-success',
            self::TIPO_AUSENTE => 'bg-warning text-dark',
            self::TIPO_DESHACER => 'bg-danger',
            self::TIPO_OMITIR => 'bg-secondary',
            default => 'bg-light text-dark',
        };
    }
}
