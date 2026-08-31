<?php

namespace App\Http\Controllers;

use App\Http\Requests\RutaRequest;
use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Empleado;
use App\Models\Ruta;
use App\Models\RutaParada;
use App\Services\GeocodingService;
use App\Services\GeneradorRutasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RutaController extends Controller
{
    protected GeneradorRutasService $generador;

    public function __construct(GeneradorRutasService $generador)
    {
        $this->generador = $generador;
    }

    public function index(Request $request): View
    {
        $user = Auth::user();
        $estadosFiltro = $request->input('estados', []);
        if (is_string($estadosFiltro)) {
            $estadosFiltro = $estadosFiltro ? explode(',', $estadosFiltro) : [];
        }
        $empleadoFiltro = $request->input('empleado_id');

        $rutas = Ruta::with(['empleado', 'user', 'paradas']);

        if ($user->role !== 'admin') {
            $empleado = $user->empleado;
            if ($empleado) {
                $rutas->where('empleado_id', $empleado->id);
            }
        }

        if (!empty($estadosFiltro)) {
            $rutas->whereIn('estado', $estadosFiltro);
        }

        if ($empleadoFiltro) {
            $rutas->where('empleado_id', $empleadoFiltro);
        }

        $rutas = $rutas->orderBy('fecha', 'desc')->paginate(15)->appends($request->all());

        $empleados = Empleado::where('estado', 'activo')->orderBy('nombre')->get();

        return view('rutas.index', compact('rutas', 'estadosFiltro', 'empleadoFiltro', 'empleados'));
    }

    public function store(RutaRequest $request): RedirectResponse
    {
        $ruta = $this->generador->generar(
            $request->contratos,
            $request->empleado_id,
            $request->fecha,
            $request->fecha_limite,
            $request->notas,
            Auth::id()
        );

        return redirect()->route('rutas.show', $ruta->id)
            ->with('success', 'Ruta creada correctamente con ' . $ruta->paradas->count() . ' paradas.');
    }

    public function show($id): View
    {
        $ruta = Ruta::with(['empleado', 'user', 'paradas.contrato.cliente', 'paradas.contrato.pagos', 'paradas.visitas'])->findOrFail($id);

        return view('rutas.show', compact('ruta'));
    }

    public function edit($id): View
    {
        $ruta = Ruta::with(['empleado', 'paradas.contrato.cliente'])->findOrFail($id);
        $empleados = Empleado::where('estado', 'activo')->orderBy('nombre')->get();

        return view('rutas.edit', compact('ruta', 'empleados'));
    }

    public function update(RutaRequest $request, $id): RedirectResponse
    {
        $ruta = Ruta::findOrFail($id);
        $ruta->update($request->validated());

        return redirect()->route('rutas.show', $ruta->id)
            ->with('success', 'Ruta actualizada correctamente.');
    }

    public function destroy($id): RedirectResponse
    {
        $ruta = Ruta::findOrFail($id);
        $ruta->delete();

        return redirect()->route('rutas.index')
            ->with('success', 'Ruta eliminada correctamente.');
    }

    public function toggleCancel($id)
    {
        $ruta = Ruta::findOrFail($id);

        if ($ruta->estado === Ruta::ESTADO_CANCELADA) {
            $paradasPendientes = $ruta->paradas()->where('estado', 'pendiente')->count();
            $nuevoEstado = $paradasPendientes > 0 ? Ruta::ESTADO_PLANEADA : Ruta::ESTADO_EN_CURSO;
            $ruta->update(['estado' => $nuevoEstado]);
            $mensaje = 'Ruta reactivada correctamente.';
        } elseif ($ruta->estado === Ruta::ESTADO_PLANEADA || $ruta->estado === Ruta::ESTADO_EN_CURSO) {
            $ruta->update(['estado' => Ruta::ESTADO_CANCELADA]);
            $mensaje = 'Ruta cancelada correctamente.';
        } else {
            return response()->json(['success' => false, 'message' => 'No se puede cambiar el estado de esta ruta.'], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $mensaje,
            'nuevo_estado' => $ruta->estado,
        ]);
    }

    public function actualizarOrden(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'orden' => 'required|array',
            'orden.*' => 'exists:ruta_paradas,id',
        ]);

        $ruta = Ruta::findOrFail($id);

        foreach ($request->orden as $index => $paradaId) {
            RutaParada::where('id', $paradaId)
                ->where('ruta_id', $ruta->id)
                ->update(['orden' => $index + 1]);
        }

        return redirect()->route('rutas.show', $ruta->id)
            ->with('success', 'Orden de paradas actualizado.');
    }

    public function actualizarEstadoParada(Request $request, $rutaId, $paradaId): RedirectResponse
    {
        $request->validate([
            'estado' => 'required|in:pendiente,visitada,omitida',
        ]);

        $parada = RutaParada::where('ruta_id', $rutaId)->findOrFail($paradaId);
        $parada->update(['estado' => $request->estado]);

        $ruta = Ruta::with('paradas')->findOrFail($rutaId);
        $todasCompletadas = $ruta->paradas->every(fn($p) => $p->estado !== 'pendiente');

        if ($todasCompletadas && $ruta->estado === Ruta::ESTADO_EN_CURSO) {
            $ruta->update(['estado' => Ruta::ESTADO_COMPLETADA]);
        } elseif ($ruta->estado === Ruta::ESTADO_PLANEADA) {
            $ruta->update(['estado' => Ruta::ESTADO_EN_CURSO]);
        }

        return back()->with('success', 'Estado de parada actualizado.');
    }

    public function geocodificarCliente(\Illuminate\Http\Request $request, GeocodingService $geocoding)
    {
        try {
            $clienteId = $request->input('cliente_id');

            if (!$clienteId) {
                return response()->json(['success' => false, 'message' => 'Falta cliente_id'], 422);
            }

            $cliente = Cliente::find($clienteId);

            if (!$cliente) {
                return response()->json(['success' => false, 'message' => 'Cliente no encontrado'], 404);
            }

            if (!$cliente->domicilio_completo) {
                return response()->json(['success' => false, 'message' => 'Sin domicilio registrado']);
            }

            $coords = $geocoding->geocode($cliente->domicilio_completo);

            if ($coords) {
                $cliente->update([
                    'latitud' => $coords['lat'],
                    'longitud' => $coords['lng'],
                ]);

                $msg = ($coords['approximate'] ?? false)
                    ? 'Ubicación aproximada (usando: ' . ($coords['query_used'] ?? '') . ')'
                    : 'Coordenadas encontradas';

                return response()->json([
                    'success' => true,
                    'lat' => $coords['lat'],
                    'lng' => $coords['lng'],
                    'approximate' => $coords['approximate'] ?? false,
                    'message' => $msg,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Sin resultados para: ' . $cliente->domicilio_completo,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function contratosEmpleado(Request $request, GeocodingService $geocoding)
    {
        $request->validate([
            'empleado_id' => 'required|string|exists:empleados,id',
        ]);

        $empleado = Empleado::findOrFail($request->empleado_id);

        $contratos = Contrato::where('estado', 'activo')
            ->whereHas('comisiones', function ($q) use ($empleado) {
                $q->where('empleado_id', $empleado->id);
            })
            ->with(['cliente', 'pagos' => function ($q) {
                $q->where('estado', 'hecho');
            }])
            ->get();

        // Geocodificar todos los clientes antes de devolver los datos
        if ($request->boolean('geocodificar')) {
            $clientesIds = $contratos->pluck('cliente_id')->unique();
            foreach ($clientesIds as $cid) {
                $cliente = Cliente::find($cid);
                if ($cliente && $cliente->domicilio_completo) {
                    $coords = $geocoding->geocode($cliente->domicilio_completo);
                    if ($coords) {
                        $cliente->update([
                            'latitud' => $coords['lat'],
                            'longitud' => $coords['lng'],
                        ]);
                    }
                }
            }
            // Recargar contratos con coordenadas actualizadas
            $contratos = Contrato::where('estado', 'activo')
                ->whereHas('comisiones', function ($q) use ($empleado) {
                    $q->where('empleado_id', $empleado->id);
                })
                ->with(['cliente', 'pagos' => function ($q) {
                    $q->where('estado', 'hecho');
                }])
                ->get();
        }

        $data = $contratos->map(function ($c) {
            $pagosHechos = $c->pagos->where('estado', 'hecho');
            $abonoPromedio = $pagosHechos->count() > 0 ? round($pagosHechos->avg('monto'), 2) : 0;

            return [
                'id' => $c->id,
                'cliente_id' => $c->cliente_id,
                'cliente_nombre' => $c->cliente->nombre . ' ' . $c->cliente->apellido,
                'cliente_telefono' => $c->cliente->telefono,
                'domicilio' => $c->cliente->domicilio_completo,
                'cuota' => number_format($c->monto_cuota_real, 2),
                'abono_promedio' => $abonoPromedio,
                'saldo' => number_format($c->saldo_pendiente, 2),
                'saldo_raw' => $c->saldo_pendiente,
                'proxima_fecha_pago' => $c->proxima_fecha_pago ? \Carbon\Carbon::parse($c->proxima_fecha_pago)->format('d/m/Y') : null,
                'pago_atrasado' => $c->proxima_fecha_pago ? \Carbon\Carbon::parse($c->proxima_fecha_pago)->isPast() : false,
                'latitud' => $c->cliente->latitud,
                'longitud' => $c->cliente->longitud,
                'tiene_coordenadas' => $c->cliente->tiene_coordenadas,
            ];
        });

        return response()->json([
            'success' => true,
            'contratos' => $data,
            'empleado' => [
                'latitud' => $empleado->latitud,
                'longitud' => $empleado->longitud,
            ],
        ]);
    }

    public function getRutaData($id, GeocodingService $geocoding)
    {
        $ruta = Ruta::with(['empleado', 'paradas.contrato.cliente'])->findOrFail($id);

        $paradas = $ruta->paradas->map(function ($p) use ($geocoding) {
            $cliente = $p->contrato->cliente ?? null;
            $domicilioActual = $cliente ? $cliente->domicilio_completo : $p->direccion_destino;

            // Si la dirección del cliente cambió desde que se creó la parada, re-geocodificar
            if ($cliente && $p->direccion_destino !== $domicilioActual) {
                $coords = $geocoding->geocode($domicilioActual);
                if ($coords) {
                    $cliente->update([
                        'latitud' => $coords['lat'],
                        'longitud' => $coords['lng'],
                    ]);
                }
                // Actualizar la parada con la nueva dirección
                $p->update([
                    'direccion_destino' => $domicilioActual,
                    'latitud' => $cliente->latitud,
                    'longitud' => $cliente->longitud,
                ]);
            }

            return [
                'id' => $p->id,
                'contrato_id' => $p->contrato_id,
                'cliente_id' => $p->cliente_id,
                'cliente_nombre' => $cliente ? ($cliente->nombre . ' ' . $cliente->apellido) : 'Sin cliente',
                'domicilio' => $domicilioActual,
                'latitud' => $cliente ? $cliente->latitud : $p->latitud,
                'longitud' => $cliente ? $cliente->longitud : $p->longitud,
                'estado' => $p->estado,
                'orden' => $p->orden,
                'cuota' => $p->contrato ? number_format($p->contrato->monto_cuota_real, 2) : '0.00',
                'saldo' => $p->contrato ? number_format($p->contrato->saldo_pendiente, 2) : '0.00',
            ];
        });

        return response()->json([
            'success' => true,
            'ruta' => [
                'id' => $ruta->id,
                'empleado_id' => $ruta->empleado_id,
                'empleado_nombre' => $ruta->empleado->nombre . ' ' . $ruta->empleado->apellido,
                'fecha' => $ruta->fecha->format('Y-m-d'),
                'fecha_limite' => $ruta->fecha_limite->format('Y-m-d'),
                'estado' => $ruta->estado,
                'notas' => $ruta->notas,
                'empleado_lat' => $ruta->empleado->latitud,
                'empleado_lng' => $ruta->empleado->longitud,
            ],
            'paradas' => $paradas,
        ]);
    }

    public function reubicarCliente(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $cliente = Cliente::findOrFail($request->cliente_id);
        $cliente->update([
            'latitud' => $request->lat,
            'longitud' => $request->lng,
        ]);

        return response()->json(['success' => true]);
    }

    public function updateAjax(Request $request, $id)
    {
        $request->validate([
            'fecha' => 'required|date',
            'fecha_limite' => 'required|date|after_or_equal:fecha',
            'estado' => 'required|in:planeada,en_curso,completada,cancelada',
            'notas' => 'nullable|string',
            'paradas' => 'nullable|array',
            'paradas.*' => 'exists:ruta_paradas,id',
        ]);

        $ruta = Ruta::findOrFail($id);
        $ruta->update([
            'fecha' => $request->fecha,
            'fecha_limite' => $request->fecha_limite,
            'estado' => $request->estado,
            'notas' => $request->notas,
        ]);

        if ($request->has('paradas')) {
            foreach ($request->paradas as $index => $paradaId) {
                RutaParada::where('id', $paradaId)
                    ->where('ruta_id', $ruta->id)
                    ->update(['orden' => $index + 1]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Ruta actualizada correctamente.',
        ]);
    }
}
