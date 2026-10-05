<?php

namespace App\Http\Controllers;

use App\Models\Ajuste;
use App\Models\Contrato;
use App\Models\Pago;
use App\Models\Ruta;
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
        if ($esEmpleado) {
            $request->validate([
                'recibido' => 'required|boolean',
                'en_domicilio' => 'required|boolean',
                'receptor_nombre' => 'required_if:recibido,true,1|nullable|string|max:120',
                'receptor_parentesco' => ['required_if:recibido,true,1', 'nullable', 'string', Rule::in(array_keys(Visita::PARENTESCOS))],
            ], [
                'receptor_nombre.required_if' => 'Indica el nombre de quien recibió.',
                'receptor_parentesco.required_if' => 'Indica el parentesco de quien recibió.',
            ]);
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

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Visita registrada con éxito.',
                'visita_id' => $primera?->id,
                'lote' => $lote,
                'ticket_url' => $primera ? route('visitas.ticket', $primera) : null,
            ]);
        }

        return back()->with('success', 'Visita registrada con éxito.');
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
        $recibido = $request->boolean('recibido');

        $visita = new Visita();
        $visita->contrato_id = $contrato->id;
        $visita->user_id = Auth::id();
        $visita->lote = $lote;
        $visita->comentarios = $request->comentarios;
        $visita->adeudo_momento = $contrato->saldo_pendiente;
        $visita->ruta_parada_id = $parada?->id;
        $visita->en_domicilio = $request->has('en_domicilio') ? $request->boolean('en_domicilio') : null;
        $visita->recibido = $recibido;
        $visita->receptor_nombre = $recibido ? $request->input('receptor_nombre') : null;
        $visita->receptor_parentesco = $recibido ? $request->input('receptor_parentesco') : null;
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
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $visita->ubicacion_evidencia = DB::raw("ST_GeomFromText('".$punto."')");

            return;
        }

        $visita->ubicacion_evidencia = $punto;
    }
}
