<?php

use App\Models\RutaActividad;
use App\Models\RutaParada;
use App\Models\User;
use App\Models\Visita;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Genera la actividad de rutas de las visitas y paradas omitidas registradas
     * antes de que existiera el log. Las visitas deshechas ya no existen y no se recuperan.
     */
    public function up(): void
    {
        $registradas = RutaActividad::query()->whereNotNull('visita_id')->pluck('visita_id')->all();

        Visita::with(['contrato.cliente', 'pagos', 'rutaParada.ruta'])
            ->orderBy('id')
            ->get()
            ->groupBy(fn (Visita $v) => $v->lote ?: 'visita-'.$v->id)
            ->each(function ($visitas) use ($registradas) {
                if ($visitas->pluck('id')->intersect($registradas)->isNotEmpty()) {
                    return;
                }

                $tipo = $visitas->flatMap->pagos->isNotEmpty()
                    ? RutaActividad::TIPO_ABONO
                    : RutaActividad::TIPO_AUSENTE;
                $datos = RutaActividad::datosDeVisitas($visitas, $tipo);
                $datos['empleado_id'] ??= User::find($datos['user_id'])?->empleado?->id;

                $this->crear($datos, $visitas->first()->created_at);
            });

        $omitidasRegistradas = RutaActividad::query()
            ->where('tipo', RutaActividad::TIPO_OMITIR)
            ->whereNotNull('ruta_parada_id')
            ->pluck('ruta_parada_id')
            ->all();

        RutaParada::with(['contrato.cliente', 'ruta'])
            ->where('estado', RutaParada::ESTADO_OMITIDA)
            ->whereNotIn('id', $omitidasRegistradas)
            ->get()
            ->each(function (RutaParada $parada) {
                $cliente = $parada->contrato?->cliente;
                $nombreCliente = $cliente
                    ? trim(($cliente->nombre ?? '').' '.($cliente->apellido ?? ''))
                    : ($parada->direccion_destino ?: 'Parada');

                $this->crear([
                    'empleado_id' => $parada->ruta?->empleado_id,
                    'ruta_id' => $parada->ruta_id,
                    'ruta_parada_id' => $parada->id,
                    'tipo' => RutaActividad::TIPO_OMITIR,
                    'titulo' => 'Omitir parada · '.$nombreCliente,
                    'detalle' => $parada->contrato_id ? 'Folio #'.$parada->contrato_id.'.' : null,
                    'meta' => [
                        'cliente' => $nombreCliente,
                        'contratos' => array_filter([$parada->contrato_id]),
                        'ruta_nombre' => $parada->ruta?->nombre,
                    ],
                ], $parada->updated_at);
            });
    }

    public function down(): void
    {
        // Sin reversa: no se distingue lo rellenado de lo registrado en vivo.
    }

    private function crear(array $datos, $fecha): void
    {
        if (empty($datos['empleado_id'])) {
            return;
        }

        $actividad = new RutaActividad($datos);
        $actividad->created_at = $fecha;
        $actividad->updated_at = $fecha;
        $actividad->save();
    }
};
