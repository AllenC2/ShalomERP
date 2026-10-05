<?php

namespace App\Services;

use App\Models\Contrato;
use App\Models\Empleado;
use App\Models\Ruta;
use App\Models\RutaParada;
use App\Models\RutaPlantilla;
use App\Models\RutaPlantillaParada;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GeneradorRutasService
{
    public function __construct(protected GeocodingService $geocoding)
    {
    }

    public function crearPlantillaEInstancia(array $datos): Ruta
    {
        $fecha = Carbon::parse($datos['fecha'])->startOfDay();

        if (($datos['frecuencia'] ?? '') === RutaPlantilla::FRECUENCIA_SEMANAL) {
            $datos['dia_semana'] = (int) $fecha->dayOfWeekIso;
        }
        if (($datos['frecuencia'] ?? '') === RutaPlantilla::FRECUENCIA_MENSUAL) {
            $datos['dia_mes'] = (int) $fecha->day;
        }

        $this->asegurarFechaCoincide($datos, $fecha);

        return DB::transaction(function () use ($datos, $fecha) {
            $empleado = Empleado::findOrFail($datos['empleado_id']);
            $this->geocodificarEmpleado($empleado);

            $contratoIds = array_values(array_unique($datos['contratos']));
            $ocupados = $this->contratosEnRutasActivas($contratoIds);
            if (!empty($ocupados)) {
                throw new InvalidArgumentException('Uno o más contratos ya están en una ruta activa: ' . implode(', ', $ocupados));
            }

            $plantilla = RutaPlantilla::create([
                'nombre' => $datos['nombre'],
                'empleado_id' => $empleado->id,
                'frecuencia' => $datos['frecuencia'],
                'dia_semana' => $datos['frecuencia'] === RutaPlantilla::FRECUENCIA_SEMANAL ? (int) $datos['dia_semana'] : null,
                'dia_mes' => $datos['frecuencia'] === RutaPlantilla::FRECUENCIA_MENSUAL ? (int) $datos['dia_mes'] : null,
                'punto_casa' => $datos['punto_casa'] ?? 'inicio',
                'activa' => true,
                'user_id' => $datos['user_id'] ?? null,
            ]);

            foreach ($contratoIds as $index => $contratoId) {
                $contrato = Contrato::with('cliente')->findOrFail($contratoId);
                RutaPlantillaParada::create([
                    'plantilla_id' => $plantilla->id,
                    'orden' => $index + 1,
                    'contrato_id' => $contrato->id,
                    'cliente_id' => $contrato->cliente_id,
                ]);
            }

            return $this->clonarInstancia($plantilla->fresh('paradas.contrato.cliente'), $fecha, $datos['notas'] ?? null);
        });
    }

    public function clonarInstancia(RutaPlantilla $plantilla, Carbon $fecha, ?string $notas = null): Ruta
    {
        $empleado = $plantilla->empleado;
        $this->geocodificarEmpleado($empleado);

        $ruta = Ruta::create([
            'plantilla_id' => $plantilla->id,
            'nombre' => $plantilla->nombre,
            'empleado_id' => $plantilla->empleado_id,
            'fecha' => $fecha->toDateString(),
            'estado' => Ruta::ESTADO_PLANEADA,
            'notas' => $notas,
            'punto_casa' => $plantilla->punto_casa ?? 'inicio',
            'user_id' => $plantilla->user_id,
        ]);

        foreach ($plantilla->paradas as $index => $paradaPlantilla) {
            $contrato = $paradaPlantilla->contrato()->with('cliente')->first();
            $cliente = $contrato?->cliente;
            if ($cliente && !$cliente->tiene_coordenadas && $cliente->domicilio_completo) {
                $coords = $this->geocoding->geocode($cliente->domicilio_completo);
                if ($coords) {
                    $cliente->update([
                        'latitud' => $coords['lat'],
                        'longitud' => $coords['lng'],
                    ]);
                    $cliente->refresh();
                }
            }

            RutaParada::create([
                'ruta_id' => $ruta->id,
                'orden' => $paradaPlantilla->orden ?: ($index + 1),
                'contrato_id' => $paradaPlantilla->contrato_id,
                'cliente_id' => $paradaPlantilla->cliente_id,
                'direccion_destino' => $cliente?->domicilio_completo ?? '',
                'latitud' => $cliente?->latitud,
                'longitud' => $cliente?->longitud,
                'estado' => RutaParada::ESTADO_PENDIENTE,
            ]);
        }

        return $ruta->load('paradas.contrato.cliente', 'empleado', 'plantilla');
    }

    public function generarInstanciasDelDia(?Carbon $fecha = null): int
    {
        $fecha = ($fecha ?? now())->startOfDay();
        $creadas = 0;

        RutaPlantilla::with(['paradas.contrato.cliente', 'empleado'])
            ->where('activa', true)
            ->get()
            ->each(function (RutaPlantilla $plantilla) use ($fecha, &$creadas) {
                if (!$plantilla->correspondeA($fecha)) {
                    return;
                }
                $existe = Ruta::where('plantilla_id', $plantilla->id)
                    ->whereDate('fecha', $fecha->toDateString())
                    ->exists();
                if ($existe) {
                    return;
                }
                $this->clonarInstancia($plantilla, $fecha);
                $creadas++;
            });

        return $creadas;
    }

    public function cerrarEjecucionesPasadas(?Carbon $hoy = null): int
    {
        $hoy = ($hoy ?? now())->startOfDay();
        $cerradas = 0;

        $rutas = Ruta::with('paradas')
            ->whereDate('fecha', '<', $hoy->toDateString())
            ->whereIn('estado', [Ruta::ESTADO_PLANEADA, Ruta::ESTADO_EN_CURSO])
            ->get();

        foreach ($rutas as $ruta) {
            $visitadas = $ruta->paradas->where('estado', RutaParada::ESTADO_VISITADA)->count();
            $total = $ruta->paradas->count();

            if ($total > 0 && $visitadas === $total) {
                $ruta->update(['estado' => Ruta::ESTADO_COMPLETADA]);
            } elseif ($visitadas === 0) {
                $ruta->update(['estado' => Ruta::ESTADO_VENCIDA]);
            } else {
                $ruta->update(['estado' => Ruta::ESTADO_INCOMPLETA]);
            }
            $cerradas++;
        }

        return $cerradas;
    }

    /**
     * Contratos presentes en instancias de ruta aún operativas (planeada / en curso).
     * Rutas detenidas, completadas, vencidas o incompletas no bloquean la selección.
     */
    public function contratosEnRutasActivas(array $contratoIds = []): array
    {
        $query = RutaParada::query()
            ->whereHas('ruta', function ($q) {
                $q->whereIn('estado', [Ruta::ESTADO_PLANEADA, Ruta::ESTADO_EN_CURSO]);
            });

        if (! empty($contratoIds)) {
            $query->whereIn('contrato_id', $contratoIds);
        }

        return $query->pluck('contrato_id')->unique()->values()->all();
    }

    /** @deprecated Usa contratosEnRutasActivas() */
    public function contratosEnPlantillasActivas(array $contratoIds = []): array
    {
        return $this->contratosEnRutasActivas($contratoIds);
    }

    public function asegurarFechaCoincide(array $datos, Carbon $fecha): void
    {
        $ok = match ($datos['frecuencia'] ?? '') {
            RutaPlantilla::FRECUENCIA_DIARIA => true,
            RutaPlantilla::FRECUENCIA_SEMANAL => (int) $fecha->dayOfWeekIso === (int) ($datos['dia_semana'] ?? 0),
            RutaPlantilla::FRECUENCIA_MENSUAL => (int) $fecha->day === min((int) ($datos['dia_mes'] ?? 0), $fecha->daysInMonth),
            default => false,
        };

        if (!$ok) {
            throw new InvalidArgumentException('La fecha no coincide con la frecuencia elegida.');
        }
    }

    protected function geocodificarEmpleado(Empleado $empleado): void
    {
        if (!$empleado->tiene_coordenadas && $empleado->domicilio) {
            $coords = $this->geocoding->geocode($empleado->domicilio);
            if ($coords) {
                $empleado->update([
                    'latitud' => $coords['lat'],
                    'longitud' => $coords['lng'],
                ]);
            }
        }
    }
}
