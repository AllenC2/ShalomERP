<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
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

    /**
     * Arma los datos de actividad para un lote de visitas (abono, ausente o deshacer).
     * Espera las visitas con contrato.cliente, pagos y rutaParada.ruta cargados.
     */
    public static function datosDeVisitas(Collection $visitas, string $tipo): array
    {
        $primera = $visitas->first();
        $parada = $visitas->first(fn (Visita $v) => $v->rutaParada)?->rutaParada;
        $ruta = $parada?->ruta;
        $pagos = $visitas->flatMap->pagos;

        $cliente = $primera?->contrato?->cliente;
        $nombreCliente = $cliente
            ? trim(($cliente->nombre ?? '').' '.($cliente->apellido ?? ''))
            : ($parada?->direccion_destino ?: 'Visita');

        $folios = $visitas->pluck('contrato_id')->filter()->unique()->values();
        $detalle = 'Folio'.($folios->count() === 1 ? '' : 's').' '.
            $folios->map(fn ($id) => '#'.$id)->implode(', ').'.';

        if ($pagos->isNotEmpty()) {
            $metodos = $pagos->pluck('metodo_pago')->unique()
                ->map(fn ($m) => Pago::METODOS_PAGO[$m] ?? $m)
                ->implode(', ');
            $detalle .= ' '.($tipo === self::TIPO_DESHACER ? 'Pago revertido' : 'Pago').' en '.mb_strtolower($metodos).'.';
        } elseif ($tipo === self::TIPO_AUSENTE) {
            $detalle .= ' Sin recepción en domicilio.';
        } elseif ($tipo === self::TIPO_DESHACER) {
            $detalle .= ' Visita sin recepción revertida.';
        }

        if ($primera?->comentarios) {
            $detalle .= ' '.$primera->comentarios;
        }

        $titulos = [
            self::TIPO_ABONO => 'Abono',
            self::TIPO_AUSENTE => 'Ausente',
            self::TIPO_DESHACER => $pagos->isNotEmpty() ? 'Deshacer abono' : 'Deshacer ausente',
        ];

        return [
            'empleado_id' => $ruta?->empleado_id,
            'user_id' => $primera?->user_id,
            'ruta_id' => $ruta?->id,
            'ruta_parada_id' => $parada?->id,
            'visita_id' => $primera?->id,
            'tipo' => $tipo,
            'titulo' => ($titulos[$tipo] ?? ucfirst($tipo)).' · '.$nombreCliente,
            'detalle' => $detalle,
            'monto_total' => $pagos->isNotEmpty() ? round((float) $pagos->sum('monto'), 2) : null,
            'meta' => [
                'cliente' => $nombreCliente,
                'contratos' => $folios->all(),
                'ruta_nombre' => $ruta?->nombre,
                'lote' => $primera?->lote,
                'tipo_original' => $tipo === self::TIPO_DESHACER
                    ? ($pagos->isNotEmpty() ? self::TIPO_ABONO : self::TIPO_AUSENTE)
                    : null,
                'visitas' => $visitas->pluck('id')->all(),
            ],
        ];
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
