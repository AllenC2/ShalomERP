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
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use InvalidArgumentException;

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

        $rutas = Ruta::with(['empleado', 'user', 'paradas', 'plantilla']);

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
        try {
            $ruta = $this->generador->crearPlantillaEInstancia([
                'nombre' => $request->nombre,
                'empleado_id' => $request->empleado_id,
                'fecha' => $request->fecha,
                'frecuencia' => $request->frecuencia,
                'contratos' => $request->contratos,
                'notas' => $request->notas,
                'punto_casa' => $request->input('punto_casa', 'inicio'),
                'user_id' => Auth::id(),
            ]);
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['contratos' => $e->getMessage()]);
        }

        return redirect()->route('rutas.show', $ruta->id)
            ->with('success', 'Ruta creada correctamente con ' . $ruta->paradas->count() . ' paradas.');
    }

    public function show($id): View
    {
        $ruta = Ruta::with(['empleado', 'user', 'plantilla', 'paradas.contrato.cliente', 'paradas.contrato.pagos', 'paradas.visitas'])->findOrFail($id);

        return view('rutas.show', compact('ruta'));
    }

    public function edit($id): View
    {
        $ruta = Ruta::with(['empleado', 'paradas.contrato.cliente'])->findOrFail($id);
        $empleados = Empleado::where('estado', 'activo')->orderBy('nombre')->get();
        if ($ruta->empleado && !$empleados->contains('id', $ruta->empleado_id)) {
            $empleados->prepend($ruta->empleado);
        }

        return view('rutas.edit', compact('ruta', 'empleados'));
    }

    public function update(RutaRequest $request, $id): RedirectResponse
    {
        $ruta = Ruta::findOrFail($id);
        $ruta->update($request->validated());
        $this->sincronizarEmpleadoPlantilla($ruta);

        return redirect()->route('rutas.show', $ruta->id)
            ->with('success', 'Ruta actualizada correctamente.');
    }

    public function destroy($id): RedirectResponse
    {
        $ruta = Ruta::findOrFail($id);
        $ruta->delete();

        return redirect()->route('rutas.index')
            ->with('success', 'Ruta eliminada correctamente. Quedó registrada como borrada.');
    }

    public function toggleDetener($id)
    {
        $ruta = Ruta::findOrFail($id);

        if ($ruta->estado === Ruta::ESTADO_DETENIDA) {
            $paradasPendientes = $ruta->paradas()->where('estado', 'pendiente')->count();
            $nuevoEstado = $paradasPendientes > 0 ? Ruta::ESTADO_PLANEADA : Ruta::ESTADO_EN_CURSO;
            $ruta->update(['estado' => $nuevoEstado]);
            $mensaje = 'Ruta reactivada correctamente.';
        } elseif ($ruta->estado === Ruta::ESTADO_PLANEADA || $ruta->estado === Ruta::ESTADO_EN_CURSO) {
            $ruta->update(['estado' => Ruta::ESTADO_DETENIDA]);
            $mensaje = 'Ruta detenida correctamente.';
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
        $this->sincronizarEstadoRutaPorParadas($ruta);

        return back()->with('success', 'Estado de parada actualizado.');
    }

    /**
     * Recalcula el estado operativo de la ruta según el avance de paradas.
     * Al revertir una visitada/omitida a pendiente, vuelve a en_curso o planeada.
     */
    protected function sincronizarEstadoRutaPorParadas(Ruta $ruta): void
    {
        // Las rutas detenidas no se reactivan al tocar paradas; solo toggleDetener.
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

    public function omitirParadas(Request $request, $rutaId)
    {
        $request->validate([
            'ruta_parada_ids' => 'required|array|min:1',
            'ruta_parada_ids.*' => 'exists:ruta_paradas,id',
        ]);

        $ruta = Ruta::with('paradas')->findOrFail($rutaId);
        $this->autorizarRutaEmpleado($ruta);

        $ids = collect($request->input('ruta_parada_ids'))->unique()->values();
        $paradas = $ruta->paradas->whereIn('id', $ids->all());

        if ($paradas->count() !== $ids->count()) {
            return response()->json([
                'success' => false,
                'message' => 'Una o más paradas no pertenecen a esta ruta.',
            ], 422);
        }

        $grupos = $this->gruposDomicilio($ruta);
        $grupoIdx = null;
        foreach ($grupos as $i => $grupo) {
            if ($grupo->pluck('id')->intersect($ids)->isNotEmpty()) {
                $grupoIdx = $i;
                break;
            }
        }

        if ($grupoIdx === null) {
            return response()->json(['success' => false, 'message' => 'No se encontró la parada.'], 422);
        }

        if ($grupoIdx > 0) {
            $anteriorPendiente = $grupos[$grupoIdx - 1]->contains(
                fn (RutaParada $p) => $p->estado === RutaParada::ESTADO_PENDIENTE
            );
            if ($anteriorPendiente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes visitar u omitir la parada anterior primero.',
                ], 422);
            }
        }

        foreach ($paradas as $parada) {
            if ($parada->estado === RutaParada::ESTADO_PENDIENTE) {
                $parada->update(['estado' => RutaParada::ESTADO_OMITIDA]);
            }
        }

        $ruta->refresh();
        $pendientes = $ruta->paradas()->where('estado', RutaParada::ESTADO_PENDIENTE)->exists();
        if (! $pendientes && in_array($ruta->estado, [Ruta::ESTADO_PLANEADA, Ruta::ESTADO_EN_CURSO], true)) {
            $ruta->update(['estado' => Ruta::ESTADO_COMPLETADA]);
        } elseif ($ruta->estado === Ruta::ESTADO_PLANEADA) {
            $ruta->update(['estado' => Ruta::ESTADO_EN_CURSO]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Parada omitida. La siguiente queda desbloqueada.',
        ]);
    }

    public function actualizarPuntoCasa(Request $request, $rutaId)
    {
        $request->validate([
            'punto_casa' => 'required|in:inicio,final,ambos',
            'ruta_parada_ids' => 'nullable|array',
            'ruta_parada_ids.*' => 'exists:ruta_paradas,id',
        ]);

        $ruta = Ruta::with('paradas')->findOrFail($rutaId);
        $this->autorizarRutaEmpleado($ruta);

        $ruta->update(['punto_casa' => $request->punto_casa]);

        $ids = collect($request->input('ruta_parada_ids', []))->map(fn ($id) => (int) $id)->unique()->values();
        foreach ($ids as $index => $paradaId) {
            RutaParada::where('id', $paradaId)
                ->where('ruta_id', $ruta->id)
                ->update(['orden' => $index + 1]);
        }

        return response()->json([
            'success' => true,
            'punto_casa' => $ruta->punto_casa,
        ]);
    }

    protected function autorizarRutaEmpleado(Ruta $ruta): void
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'empleado') {
            return;
        }

        $empleadoId = $user->empleado?->id;
        if (! $empleadoId || (int) $ruta->empleado_id !== (int) $empleadoId) {
            abort(403, 'No puedes modificar una ruta que no te corresponde.');
        }
    }

    protected function gruposDomicilio(Ruta $ruta)
    {
        $grupos = [];
        $orden = [];
        foreach ($ruta->paradas->sortBy('orden') as $parada) {
            $key = $parada->cliente_id . '_' . $parada->direccion_destino;
            if (! isset($grupos[$key])) {
                $grupos[$key] = collect();
                $orden[] = $key;
            }
            $grupos[$key]->push($parada);
        }

        return array_map(fn ($key) => $grupos[$key], $orden);
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

    public function empleadoDatos(Request $request, GeocodingService $geocoding)
    {
        $request->validate([
            'empleado_id' => 'required|string|exists:empleados,id',
        ]);

        $empleado = Empleado::findOrFail($request->empleado_id);

        if (!$empleado->tiene_coordenadas && $empleado->domicilio) {
            $coords = $geocoding->geocode($empleado->domicilio);
            if ($coords) {
                $empleado->update([
                    'latitud' => $coords['lat'],
                    'longitud' => $coords['lng'],
                ]);
                $empleado->refresh();
            }
        }

        return response()->json([
            'success' => true,
            'empleado' => [
                'latitud' => $empleado->latitud,
                'longitud' => $empleado->longitud,
                'domicilio' => $empleado->domicilio,
            ],
        ]);
    }

    public function buscarContratos(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        if ($q === '') {
            return response()->json(['success' => true, 'contratos' => []]);
        }

        $ocupados = $this->generador->contratosEnRutasActivas();

        $contratos = Contrato::query()
            ->whereRaw('LOWER(TRIM(contratos.estado)) = ?', [Contrato::ESTADO_ACTIVO])
            ->where(function ($query) use ($q) {
                $query->where('id', 'like', $q . '%')
                    ->orWhereHas('cliente', function ($cliente) use ($q) {
                        $cliente->where('nombre', 'like', '%' . $q . '%')
                            ->orWhere('apellido', 'like', '%' . $q . '%');
                    });
            })
            ->with(['cliente', 'pagos' => function ($query) {
                $query->where('estado', 'hecho');
            }])
            ->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$q])
            ->orderBy('id')
            ->limit(12)
            ->get();

        return response()->json([
            'success' => true,
            'contratos' => $contratos
                ->filter(fn (Contrato $c) => strtolower(trim((string) $c->estado)) === Contrato::ESTADO_ACTIVO)
                ->map(function (Contrato $c) use ($ocupados) {
                    $row = $this->serializarContratoRuta($c);
                    $row['estado'] = $c->estado;
                    $row['ocupado'] = in_array($c->id, $ocupados, false);

                    return $row;
                })->values(),
        ]);
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

        $data = $contratos->map(fn (Contrato $c) => $this->serializarContratoRuta($c));

        return response()->json([
            'success' => true,
            'contratos' => $data,
            'empleado' => [
                'latitud' => $empleado->latitud,
                'longitud' => $empleado->longitud,
                'domicilio' => $empleado->domicilio,
            ],
        ]);
    }

    public function getRutaData($id, GeocodingService $geocoding)
    {
        $ruta = Ruta::with(['empleado', 'plantilla', 'paradas.contrato.cliente'])->findOrFail($id);

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

            $row = [
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
                'saldo_raw' => $p->contrato ? (float) $p->contrato->saldo_pendiente : 0,
            ];

            // El saldo total no se expone a empleados en la UI.
            if (Auth::user()?->role === 'admin') {
                $row['saldo'] = $p->contrato ? number_format($p->contrato->saldo_pendiente, 2) : '0.00';
                $row['monto_total'] = $p->contrato ? (float) $p->contrato->monto_total : 0;
            }

            return $row;
        });

        $esPasada = $ruta->fecha && $ruta->fecha->lt(Carbon::today());

        return response()->json([
            'success' => true,
            'ruta' => [
                'id' => $ruta->id,
                'empleado_id' => $ruta->empleado_id,
                'empleado_nombre' => $ruta->empleado->nombre . ' ' . $ruta->empleado->apellido,
                'fecha' => optional($ruta->fecha)->format('Y-m-d'),
                'nombre' => $ruta->nombre,
                'frecuencia' => $ruta->plantilla?->etiquetaFrecuencia(),
                'estado' => $ruta->estado,
                'notas' => $ruta->notas,
                'punto_casa' => $ruta->punto_casa ?? 'inicio',
                'empleado_lat' => $ruta->empleado->latitud,
                'empleado_lng' => $ruta->empleado->longitud,
                'es_pasada' => $esPasada,
                'resumen' => $this->resumenRutaHistorica($ruta, $paradas),
            ],
            'paradas' => $paradas,
        ]);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $paradas
     * @return array<string, mixed>
     */
    protected function resumenRutaHistorica(Ruta $ruta, $paradas): array
    {
        $grupos = [];
        foreach ($paradas as $p) {
            $key = ($p['cliente_id'] ?? '') . '_' . ($p['domicilio'] ?? '');
            if (! isset($grupos[$key])) {
                $grupos[$key] = [
                    'nombre' => $p['cliente_nombre'] ?? 'Sin cliente',
                    'paradas' => [],
                ];
            }
            $grupos[$key]['paradas'][] = $p;
        }

        $visitados = 0;
        $omitidos = 0;
        $pendientes = 0;
        $hastaNombre = null;
        $hastaOrden = 0;
        $secuenciaRota = false;

        foreach (array_values($grupos) as $i => $grupo) {
            $totalGrupo = count($grupo['paradas']);
            $vis = collect($grupo['paradas'])->where('estado', RutaParada::ESTADO_VISITADA)->count();
            $omi = collect($grupo['paradas'])->where('estado', RutaParada::ESTADO_OMITIDA)->count();
            $cerrada = $totalGrupo > 0 && ($vis + $omi) === $totalGrupo;

            if ($cerrada) {
                if ($vis > 0) {
                    $visitados++;
                } else {
                    $omitidos++;
                }
            } else {
                $pendientes++;
            }

            if (! $secuenciaRota && $cerrada) {
                $hastaNombre = $grupo['nombre'];
                $hastaOrden = $i + 1;
            } elseif (! $cerrada) {
                $secuenciaRota = true;
            }
        }

        $total = count($grupos);
        $hechos = $visitados + $omitidos;
        $comenzada = $hechos > 0 || collect($paradas)->contains(function ($p) {
            return in_array($p['estado'] ?? '', [RutaParada::ESTADO_VISITADA, RutaParada::ESTADO_OMITIDA], true);
        });
        $completada = $total > 0 && $pendientes === 0;

        if ($total === 0) {
            $headline = 'Sin paradas';
            $detalle = 'Esta ruta no tiene domicilios registrados.';
        } elseif (! $comenzada) {
            $headline = 'No se comenzó';
            $detalle = 'No se visitó ni se omitió ningún domicilio.';
        } elseif ($completada && $omitidos === 0) {
            $headline = 'Ruta completada';
            $detalle = $total === 1
                ? 'Se visitó el domicilio asignado.'
                : 'Se visitaron los '.$total.' domicilios.';
        } elseif ($completada) {
            $headline = 'Ruta completada';
            $detalle = $visitados.' visitado'.($visitados === 1 ? '' : 's').' · '.$omitidos.' omitido'.($omitidos === 1 ? '' : 's').'.';
        } elseif ($hastaNombre) {
            $headline = 'Se comenzó y no se terminó';
            $detalle = 'Llegó hasta '.$hastaNombre.' ('.$hastaOrden.' de '.$total.'). Quedaron '.$pendientes.' pendiente'.($pendientes === 1 ? '' : 's').'.';
        } else {
            $headline = 'Se comenzó y no se terminó';
            $detalle = 'Hubo avance parcial. Quedaron '.$pendientes.' domicilio'.($pendientes === 1 ? '' : 's').' pendiente'.($pendientes === 1 ? '' : 's').'.';
        }

        return [
            'comenzada' => $comenzada,
            'completada' => $completada,
            'headline' => $headline,
            'detalle' => $detalle,
            'hasta' => $hastaNombre,
            'total' => $total,
            'visitados' => $visitados,
            'omitidos' => $omitidos,
            'pendientes' => $pendientes,
            'estado' => $ruta->estado,
        ];
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
            'nombre' => 'nullable|string|max:120',
            'empleado_id' => 'required|string|exists:empleados,id',
            'notas' => 'nullable|string',
            'paradas' => 'nullable|array',
            'paradas.*' => 'exists:ruta_paradas,id',
            'punto_casa' => 'nullable|in:inicio,final,ambos',
        ]);

        $ruta = Ruta::findOrFail($id);
        $ruta->update([
            'fecha' => $request->fecha,
            'nombre' => $request->nombre ?: $ruta->nombre,
            'empleado_id' => $request->empleado_id,
            'notas' => $request->notas,
            'punto_casa' => $request->input('punto_casa', $ruta->punto_casa ?? 'inicio'),
        ]);
        $this->sincronizarEmpleadoPlantilla($ruta);

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

    protected function serializarContratoRuta(Contrato $c): array
    {
        $pagosHechos = $c->pagos->where('estado', 'hecho');
        $abonoPromedio = $pagosHechos->count() > 0 ? round($pagosHechos->avg('monto'), 2) : 0;
        $cliente = $c->cliente;

        return [
            'id' => $c->id,
            'folio' => $c->id,
            'cliente_id' => $c->cliente_id,
            'cliente_nombre' => $cliente ? ($cliente->nombre . ' ' . $cliente->apellido) : 'Sin cliente',
            'cliente_telefono' => $cliente?->telefono,
            'domicilio' => $cliente?->domicilio_completo ?? '',
            'cuota' => number_format($c->monto_cuota_real, 2),
            'abono_promedio' => $abonoPromedio,
            'saldo' => number_format($c->saldo_pendiente, 2),
            'saldo_raw' => $c->saldo_pendiente,
            'proxima_fecha_pago' => $c->proxima_fecha_pago ? \Carbon\Carbon::parse($c->proxima_fecha_pago)->format('d/m/Y') : null,
            'pago_atrasado' => $c->proxima_fecha_pago ? \Carbon\Carbon::parse($c->proxima_fecha_pago)->isPast() : false,
            'latitud' => $cliente?->latitud,
            'longitud' => $cliente?->longitud,
            'tiene_coordenadas' => $cliente?->tiene_coordenadas ?? false,
        ];
    }

    protected function sincronizarEmpleadoPlantilla(Ruta $ruta): void
    {
        if (!$ruta->empleado_id) {
            return;
        }

        $ruta->loadMissing('plantilla');
        if ($ruta->plantilla && $ruta->plantilla->empleado_id !== $ruta->empleado_id) {
            $ruta->plantilla->update(['empleado_id' => $ruta->empleado_id]);
        }
    }
}
