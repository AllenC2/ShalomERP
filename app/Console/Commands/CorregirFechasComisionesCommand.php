<?php

namespace App\Console\Commands;

use App\Models\Comisione;
use App\Models\Contrato;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CorregirFechasComisionesCommand extends Command
{
    protected $signature = 'comisiones:corregir-fechas
                            {--dry-run : Mostrar cambios sin persistir}
                            {--execute : Persistir correcciones}';

    protected $description = 'Alinea fecha_comision de PARCIALIDAD con fecha_pago del abono inferido y restaura la fecha de las comisiones padre';

    private int $ventanaSegundos = 5;

    public function handle(): int
    {
        $execute = $this->option('execute');
        $dryRun = $this->option('dry-run') || !$execute;

        if ($execute && $this->option('dry-run')) {
            $this->error('Usa --dry-run o --execute, no ambos.');
            return self::FAILURE;
        }

        if ($dryRun) {
            $this->info('Modo dry-run: no se persistirá ningún cambio.');
        } else {
            $this->warn('Modo execute: se actualizarán registros.');
        }

        $hasPagoId = Schema::hasColumn('comisiones', 'pago_id');
        $actualizadas = 0;
        $sinMatch = 0;
        $filas = [];

        $contratos = Contrato::query()
            ->whereHas('comisiones')
            ->with('pagos')
            ->orderBy('id')
            ->get();

        foreach ($contratos as $contrato) {
            $resultado = $this->corregirContrato($contrato, $hasPagoId);

            foreach ($resultado['cambios'] as $cambio) {
                $filas[] = $cambio;
                $actualizadas++;
            }
            $sinMatch += count($resultado['unmatched']);
            foreach ($resultado['unmatched'] as $u) {
                $this->warn("Sin match contrato #{$contrato->id} comisión #{$u['id']}: {$u['motivo']}");
            }

            if (!$dryRun && count($resultado['cambios']) > 0) {
                DB::transaction(function () use ($resultado, $hasPagoId) {
                    foreach ($resultado['cambios'] as $cambio) {
                        $data = ['fecha_comision' => $cambio['nueva']];
                        if ($hasPagoId && !empty($cambio['pago_id'])) {
                            $data['pago_id'] = $cambio['pago_id'];
                        }
                        Comisione::where('id', $cambio['id'])->update($data);
                    }
                });
            }
        }

        $this->table(
            ['comision_id', 'contrato_id', 'tipo', 'confianza', 'anterior', 'nueva', 'pago_id'],
            collect($filas)->map(fn ($f) => [
                $f['id'],
                $f['contrato_id'],
                $f['tipo'],
                $f['confianza'],
                $f['anterior'],
                $f['nueva'],
                $f['pago_id'] ?? '',
            ])->all()
        );

        $this->info("Filas a corregir: {$actualizadas}. Sin match: {$sinMatch}.");

        return self::SUCCESS;
    }

    private function corregirContrato(Contrato $contrato, bool $hasPagoId): array
    {
        $cambios = [];
        $unmatched = [];

        $pagos = $contrato->pagos
            ->filter(function ($pago) {
                if ($pago->estado !== 'hecho') {
                    return false;
                }
                $tipo = strtolower((string) $pago->tipo_pago);
                return !in_array($tipo, ['inicial', 'bonificación', 'bonificacion'], true);
            })
            ->sortBy([
                ['created_at', 'asc'],
                ['id', 'asc'],
            ])
            ->values();

        $parcialidades = Comisione::query()
            ->where('contrato_id', $contrato->id)
            ->where('tipo_comision', 'PARCIALIDAD')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $asignadas = [];

        foreach ($parcialidades as $parcialidad) {
            $candidatos = $pagos->filter(function ($pago) use ($parcialidad) {
                if (!$pago->created_at || !$parcialidad->created_at) {
                    return false;
                }
                return abs($pago->created_at->diffInSeconds($parcialidad->created_at)) <= $this->ventanaSegundos;
            });

            if ($candidatos->count() === 1) {
                $pago = $candidatos->first();
                $cambio = $this->cambioParcialidad($parcialidad, $pago, 'ventana', $contrato->id);
                if ($cambio) {
                    $cambios[] = $cambio;
                }
                $asignadas[$parcialidad->id] = true;
            } elseif ($candidatos->count() > 1) {
                $unmatched[] = [
                    'id' => $parcialidad->id,
                    'motivo' => 'varios pagos en la misma ventana de created_at',
                ];
                $asignadas[$parcialidad->id] = true;
            }
        }

        $restantesPago = $pagos->mapWithKeys(function ($pago) {
            return [$pago->id => (float) $pago->monto];
        })->all();

        foreach ($parcialidades as $parcialidad) {
            if (isset($asignadas[$parcialidad->id])) {
                continue;
            }

            $monto = (float) $parcialidad->monto;
            $pagoElegido = null;
            foreach ($pagos as $pago) {
                if (($restantesPago[$pago->id] ?? 0) > 0.009) {
                    $pagoElegido = $pago;
                    $restantesPago[$pago->id] = max(0, $restantesPago[$pago->id] - $monto);
                    break;
                }
            }

            if (!$pagoElegido) {
                $unmatched[] = [
                    'id' => $parcialidad->id,
                    'motivo' => 'sin abono financiador restante (FIFO)',
                ];
                continue;
            }

            $cambio = $this->cambioParcialidad($parcialidad, $pagoElegido, 'fifo', $contrato->id);
            if ($cambio) {
                $cambios[] = $cambio;
            }
        }

        $fechaInicio = $contrato->fecha_inicio
            ? Carbon::parse($contrato->fecha_inicio)
            : null;

        if ($fechaInicio) {
            $padres = Comisione::query()
                ->where('contrato_id', $contrato->id)
                ->whereNull('comision_padre_id')
                ->where(function ($q) {
                    $q->whereNull('tipo_comision')
                        ->orWhere(function ($q2) {
                            $q2->where('tipo_comision', '!=', 'PARCIALIDAD')
                                ->where('tipo_comision', 'NOT LIKE', 'Fija - %');
                        });
                })
                ->get();

            foreach ($padres as $padre) {
                $actual = $padre->fecha_comision
                    ? Carbon::parse($padre->fecha_comision)->toDateString()
                    : null;
                $objetivo = $fechaInicio->toDateString();
                if ($actual !== $objetivo) {
                    $cambios[] = [
                        'id' => $padre->id,
                        'contrato_id' => $contrato->id,
                        'tipo' => $padre->tipo_comision,
                        'confianza' => 'padre',
                        'anterior' => $padre->fecha_comision,
                        'nueva' => $fechaInicio->copy()->startOfDay(),
                        'pago_id' => $hasPagoId ? $padre->pago_id : null,
                    ];
                }
            }
        }

        return compact('cambios', 'unmatched');
    }

    private function cambioParcialidad(Comisione $parcialidad, $pago, string $confianza, int $contratoId): ?array
    {
        $nueva = Carbon::parse($pago->fecha_pago);
        $anterior = $parcialidad->fecha_comision
            ? Carbon::parse($parcialidad->fecha_comision)
            : null;

        $mismaFecha = $anterior && $anterior->equalTo($nueva);
        $tienePago = !empty($parcialidad->pago_id);

        if ($mismaFecha && $tienePago) {
            return null;
        }

        return [
            'id' => $parcialidad->id,
            'contrato_id' => $contratoId,
            'tipo' => 'PARCIALIDAD',
            'confianza' => $confianza,
            'anterior' => $parcialidad->fecha_comision,
            'nueva' => $nueva,
            'pago_id' => $pago->id,
        ];
    }
}
