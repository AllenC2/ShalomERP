<?php

namespace App\Http\Controllers;

use App\Models\Ajuste;
use App\Models\Comisione;
use App\Models\Contrato;
use App\Models\Pago;
use App\Models\Ruta;
use App\Models\RutaActividad;
use App\Models\RutaParada;
use App\Models\Visita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VisitaController extends Controller
{
    public function store(Request $request)
    {
        $metodos = implode(',', array_keys(Pago::METODOS_PAGO));
        $tipos = 'cuota,parcialidad';

        $request->validate([
            'contrato_id' => 'required_without:ruta_parada_ids|nullable|exists:contratos,id',
            'comentarios' => 'nullable|string',
            'ubicacion_evidencia' => ['required', 'string', 'regex:/^POINT\(\s*-?\d+(\.\d+)?\s+-?\d+(\.\d+)?\s*\)$/i'],
            'ruta_parada_id' => 'nullable|exists:ruta_paradas,id',
            'ruta_parada_ids' => 'nullable|array',
            'ruta_parada_ids.*' => 'exists:ruta_paradas,id',
            'en_domicilio' => 'nullable|boolean',
            'recibido' => 'nullable|boolean',
            'ausente' => 'nullable|boolean',
            'receptor_nombre' => 'nullable|string|max:120',
            'receptor_parentesco' => ['nullable', 'string', Rule::in(array_keys(Visita::PARENTESCOS))],
            'registrar_pago' => 'nullable|boolean',
            'pagos' => 'nullable|array',
            'pagos.*.contrato_id' => 'required_with:pagos|exists:contratos,id',
            'pagos.*.monto' => 'required_with:pagos|numeric|min:0.01',
            'pagos.*.metodo_pago' => 'required_with:pagos|string|in:'.$metodos,
            'pagos.*.tipo_pago' => 'nullable|string|in:'.$tipos,
            'pagos.*.observaciones' => 'nullable|string|max:500',
        ]);

        $esEmpleado = Auth::user()?->role === 'empleado';
        $esAusente = $request->boolean('ausente');
        if ($esEmpleado) {
            $request->validate([
                'en_domicilio' => 'required|boolean',
            ]);

            if ($esAusente) {
                // Visita sin recepción: marca parada y genera aviso, sin cobro.
                $request->merge([
                    'registrar_pago' => false,
                    'recibido' => false,
                    'pagos' => [],
                ]);
            } else {
                $request->validate([
                    'pagos' => 'required|array|min:1',
                    'pagos.*.contrato_id' => 'required|exists:contratos,id',
                    'pagos.*.monto' => 'required|numeric|min:0.01',
                    'pagos.*.metodo_pago' => 'required|string|in:'.$metodos,
                ], [
                    'pagos.required' => 'Debes registrar al menos un pago o abono.',
                    'pagos.min' => 'Debes registrar al menos un pago o abono.',
                ]);
                // Flujo de cobrador con abono: siempre registra pago.
                $request->merge(['registrar_pago' => true]);
            }
        }

        $paradaIds = collect($request->input('ruta_parada_ids', []))
            ->when($request->ruta_parada_id, fn ($ids) => $ids->push($request->ruta_parada_id))
            ->filter()
            ->unique()
            ->values();

        $paradas = $paradaIds->isNotEmpty()
            ? RutaParada::with('ruta')->whereIn('id', $paradaIds)->get()
            : collect();

        if ($paradas->isEmpty() && ! $request->contrato_id) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Selecciona un domicilio para registrar la visita.'], 422)
                : back()->withErrors(['ruta_parada_ids' => 'Selecciona un domicilio para registrar la visita.']);
        }

        $this->autorizarParadas($paradas);

        $foliosAsignados = $paradas->pluck('contrato_id')->filter()->unique()->map(fn ($id) => (int) $id)->all();
        $pagosInput = collect($request->input('pagos', []));
        if ($request->boolean('registrar_pago') && $pagosInput->isEmpty()) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Indica el pago o abono de al menos un folio asignado.'], 422)
                : back()->withErrors(['pagos' => 'Indica el pago o abono de al menos un folio asignado.']);
        }

        foreach ($pagosInput as $pagoRow) {
            $cid = (int) ($pagoRow['contrato_id'] ?? 0);
            if ($paradas->isNotEmpty() && ! in_array($cid, $foliosAsignados, true)) {
                abort(403, 'Solo puedes cobrar folios de contratos asignados a esta parada.');
            }
        }

        $montoError = $this->validarMontosContraContrato($pagosInput);
        if ($montoError) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $montoError, 'errors' => ['monto' => [$montoError]]], 422)
                : back()->withErrors(['monto' => $montoError]);
        }

        $lote = (string) Str::uuid();
        $visitasCreadas = collect();

        DB::transaction(function () use ($paradas, $request, $lote, $pagosInput, &$visitasCreadas) {
            if ($paradas->isEmpty()) {
                $visitasCreadas->push($this->crearVisita($request->contrato_id, null, $request, $lote));
            } else {
                foreach ($paradas as $parada) {
                    $visitasCreadas->push($this->crearVisita($parada->contrato_id, $parada, $request, $lote));
                }
                $this->refrescarEstadoRuta($paradas->first());
            }

            $visitasPorContrato = $visitasCreadas->keyBy('contrato_id');
            foreach ($pagosInput as $pagoRow) {
                $visita = $visitasPorContrato->get((int) $pagoRow['contrato_id']) ?? $visitasCreadas->first();
                $this->crearPagoDeVisita($visita, $pagoRow);
            }
        });

        $primera = $visitasCreadas->first();

        $visitasCreadas->each->load(['contrato.cliente', 'pagos', 'rutaParada.ruta']);
        RutaActividad::registrar(RutaActividad::datosDeVisitas(
            $visitasCreadas,
            $visitasCreadas->flatMap->pagos->isNotEmpty() ? RutaActividad::TIPO_ABONO : RutaActividad::TIPO_AUSENTE
        ));

        $mensaje = $esAusente
            ? 'Visita sin recepción registrada con éxito.'
            : 'Visita registrada con éxito.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'visita_id' => $primera?->id,
                'lote' => $lote,
                'ticket_url' => $primera ? route('visitas.ticket', $primera) : null,
            ]);
        }

        return back()->with('success', $mensaje);
    }

    public function ticket(Visita $visita)
    {
        $this->autorizarTicket($visita);

        $visita->load(['contrato.cliente', 'contrato.paquete', 'rutaParada.ruta', 'user', 'pagos']);

        $visitas = $visita->lote
            ? Visita::with(['contrato.cliente', 'contrato.paquete', 'pagos'])
                ->where('lote', $visita->lote)
                ->orderBy('id')
                ->get()
            : collect([$visita]);

        $pagos = $visitas->flatMap->pagos;
        $empresa = Ajuste::obtenerInfoEmpresa();
        $empleadoNombre = $visita->user?->name ?? Auth::user()?->name;

        return view('visita.ticket', compact('visita', 'visitas', 'pagos', 'empresa', 'empleadoNombre'));
    }

    public function deshacer(Visita $visita)
    {
        $visita->load(['rutaParada.ruta', 'pagos', 'contrato']);
        $this->autorizarTicket($visita);

        $visitas = $visita->lote
            ? Visita::with(['rutaParada.ruta', 'pagos', 'contrato'])
                ->where('lote', $visita->lote)
                ->orderBy('id')
                ->get()
            : collect([$visita]);

        $rutaIds = collect();

        $visitas->each->loadMissing('contrato.cliente');
        $actividad = RutaActividad::datosDeVisitas($visitas, RutaActividad::TIPO_DESHACER);
        $actividad['user_id'] = Auth::id();

        DB::transaction(function () use ($visitas, &$rutaIds) {
            foreach ($visitas as $v) {
                foreach ($v->pagos as $pago) {
                    $this->revertirPagoDeVisita($pago);
                }

                if ($v->rutaParada) {
                    $rutaIds->push($v->rutaParada->ruta_id);
                    if ($v->rutaParada->estado === RutaParada::ESTADO_VISITADA) {
                        $v->rutaParada->update(['estado' => RutaParada::ESTADO_PENDIENTE]);
                    }
                }

                $v->delete();
            }

            $rutaIds->unique()->filter()->each(function ($rutaId) {
                $ruta = Ruta::with('paradas')->find($rutaId);
                if ($ruta) {
                    $this->sincronizarEstadoRutaPorParadas($ruta);
                }
            });
        });

        RutaActividad::registrar($actividad);

        return response()->json([
            'success' => true,
            'message' => 'Visita deshecha. La parada volvió a pendiente.',
        ]);
    }

    protected function autorizarTicket(Visita $visita): void
    {
        $user = Auth::user();
        if (! $user) {
            abort(401);
        }
        if ($user->role === 'admin') {
            return;
        }
        if ((int) $visita->user_id === (int) $user->id) {
            return;
        }
        $empleadoId = $user->empleado?->id;
        $rutaEmpleado = $visita->rutaParada?->ruta?->empleado_id;
        if ($empleadoId && $rutaEmpleado && (string) $rutaEmpleado === (string) $empleadoId) {
            return;
        }
        abort(403, 'No puedes ver este ticket.');
    }

    /**
     * Anula un pago ligado a la visita y revierte parcialidades de comisión.
     */
    protected function revertirPagoDeVisita(Pago $pago): void
    {
        $montoPago = (float) $pago->monto;
        $contrato = $pago->contrato;
        $pago->delete();

        if (! $contrato || $montoPago <= 0) {
            return;
        }

        $parcialidades = Comisione::where('contrato_id', $contrato->id)
            ->where('tipo_comision', 'PARCIALIDAD')
            ->orderBy('created_at', 'desc')
            ->orderBy('orden', 'desc')
            ->get();

        $montoARevertir = $montoPago;
        foreach ($parcialidades as $parcialidad) {
            if ($montoARevertir <= 0.009) {
                break;
            }

            $padre = $parcialidad->comision_padre_id
                ? Comisione::find($parcialidad->comision_padre_id)
                : null;

            if ((float) $parcialidad->monto <= $montoARevertir) {
                $montoARevertir -= (float) $parcialidad->monto;
                $parcialidad->delete();
            } else {
                $parcialidad->update(['monto' => (float) $parcialidad->monto - $montoARevertir]);
                $montoARevertir = 0;
            }

            if ($padre && $padre->estado === 'Pagada') {
                $padre->update(['estado' => 'Pendiente']);
            }
        }

        $contrato->actualizarProximaFechaPago();
    }

    /**
     * Recalcula el estado de la ruta al volver paradas a pendiente.
     */
    protected function sincronizarEstadoRutaPorParadas(Ruta $ruta): void
    {
        if ($ruta->estado === Ruta::ESTADO_DETENIDA) {
            return;
        }

        $paradas = $ruta->paradas;
        if ($paradas->isEmpty()) {
            return;
        }

        $hayPendientes = $paradas->contains(fn ($p) => $p->estado === RutaParada::ESTADO_PENDIENTE);
        $hayAvance = $paradas->contains(fn ($p) => in_array($p->estado, [
            RutaParada::ESTADO_VISITADA,
            RutaParada::ESTADO_OMITIDA,
        ], true));

        if (! $hayPendientes) {
            $nuevo = Ruta::ESTADO_COMPLETADA;
        } elseif ($hayAvance) {
            $nuevo = Ruta::ESTADO_EN_CURSO;
        } else {
            $nuevo = Ruta::ESTADO_PLANEADA;
        }

        if ($ruta->estado !== $nuevo) {
            $ruta->update(['estado' => $nuevo]);
        }
    }

    protected function autorizarParadas($paradas): void
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'empleado' || $paradas->isEmpty()) {
            return;
        }

        $empleadoId = $user->empleado?->id;
        foreach ($paradas as $parada) {
            if (! $empleadoId || (string) $parada->ruta?->empleado_id !== (string) $empleadoId) {
                abort(403, 'No puedes registrar visitas de una ruta que no te corresponde.');
            }
        }
    }

    protected function crearVisita(int $contratoId, ?RutaParada $parada, Request $request, string $lote): Visita
    {
        $contrato = Contrato::findOrFail($contratoId);
        $visita = new Visita();
        $visita->contrato_id = $contrato->id;
        $visita->user_id = Auth::id();
        $visita->lote = $lote;
        $visita->comentarios = $request->comentarios;
        $visita->adeudo_momento = $contrato->saldo_pendiente;
        $visita->ruta_parada_id = $parada?->id;
        $visita->en_domicilio = $request->has('en_domicilio') ? $request->boolean('en_domicilio') : null;
        // Cobro en ruta ya no captura receptor.
        $visita->recibido = false;
        $visita->receptor_nombre = null;
        $visita->receptor_parentesco = null;
        $this->asignarUbicacion($visita, $request->ubicacion_evidencia);
        $visita->save();

        if ($parada && $parada->estado === RutaParada::ESTADO_PENDIENTE) {
            $parada->update(['estado' => RutaParada::ESTADO_VISITADA]);
        }

        return $visita;
    }

    protected function crearPagoDeVisita(Visita $visita, array $pagoRow): Pago
    {
        $pago = Pago::create([
            'contrato_id' => (int) $pagoRow['contrato_id'],
            'visita_id' => $visita->id,
            'tipo_pago' => 'cuota',
            'metodo_pago' => $pagoRow['metodo_pago'],
            'monto' => $pagoRow['monto'],
            'fecha_pago' => now(),
            'observaciones' => $pagoRow['observaciones'] ?? null,
            'estado' => 'hecho',
            'created_by' => Auth::id(),
        ]);

        if ($pago->contrato) {
            $pago->contrato->actualizarProximaFechaPago();
            if ($pago->estado === 'hecho') {
                $pago->contrato->distribuirComisiones($pago);
            }
        }

        return $pago;
    }

    protected function validarMontosContraContrato($pagosInput): ?string
    {
        $porContrato = $pagosInput->groupBy(fn ($row) => (int) $row['contrato_id']);
        foreach ($porContrato as $contratoId => $rows) {
            $contrato = Contrato::with('pagos')->find($contratoId);
            if (! $contrato) {
                continue;
            }
            $extra = 0;
            foreach ($rows as $row) {
                $extra += (float) $row['monto'];
            }
            $pagado = (float) calcularMontoPagadoContrato($contrato->pagos);
            $limite = round((float) $contrato->monto_total, 2);
            $nuevo = round($pagado + $extra, 2);
            if ($nuevo > $limite) {
                if (Auth::user()?->role === 'empleado') {
                    return 'El monto del folio #'.$contrato->id.' supera el saldo disponible del contrato.';
                }

                $saldo = max(0, round($limite - $pagado, 2));

                return 'El pago del folio #'.$contrato->id.' haría que lo cobrado ($'.number_format($nuevo, 2).') supere el total del contrato ($'.number_format($limite, 2).'). El saldo disponible es $'.number_format($saldo, 2).'.';
            }
        }

        return null;
    }

    protected function refrescarEstadoRuta(?RutaParada $parada): void
    {
        if (! $parada?->ruta) {
            return;
        }

        $ruta = $parada->ruta;
        $pendientes = $ruta->paradas()->where('estado', RutaParada::ESTADO_PENDIENTE)->exists();

        if (! $pendientes && in_array($ruta->estado, [Ruta::ESTADO_PLANEADA, Ruta::ESTADO_EN_CURSO], true)) {
            $ruta->update(['estado' => Ruta::ESTADO_COMPLETADA]);
        } elseif ($ruta->estado === Ruta::ESTADO_PLANEADA) {
            $ruta->update(['estado' => Ruta::ESTADO_EN_CURSO]);
        }
    }

    protected function asignarUbicacion(Visita $visita, string $punto): void
    {
        if (preg_match('/POINT\(\s*(-?\d+(?:\.\d+)?)\s+(-?\d+(?:\.\d+)?)\s*\)/i', $punto, $matches)) {
            $visita->longitud = (float) $matches[1];
            $visita->latitud = (float) $matches[2];
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            // Revalidar WKT y escapar antes de interpolar en ST_GeomFromText.
            if (! preg_match('/^POINT\(\s*-?\d+(?:\.\d+)?\s+-?\d+(?:\.\d+)?\s*\)$/i', $punto)) {
                throw new \InvalidArgumentException('Ubicación WKT inválida.');
            }
            $visita->ubicacion_evidencia = DB::raw('ST_GeomFromText('.DB::getPdo()->quote($punto).')');

            return;
        }

        $visita->ubicacion_evidencia = $punto;
    }
}
