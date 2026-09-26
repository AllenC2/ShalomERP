<?php

namespace App\Http\Controllers;


use App\Models\Comisione;
use App\Models\Contrato;
use App\Models\Empleado;
use App\Models\Pago;
use App\Models\Ruta;
use App\Models\RutaPlantilla;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        // Obtener los últimos 7 días
        $dias = collect();
        for ($i = 6; $i >= 0; $i--) {
            $dias->push(now()->subDays($i)->format('Y-m-d'));
        }

        // Consultar la cantidad de contratos por día
        $contratosPorDia = $dias->map(function ($fecha) {
            return
                [
                    'fecha' => $fecha,
                    'cantidad' => \App\Models\Contrato::whereDate('created_at', $fecha)->count()
                ];
        });

        // Solo los valores para el gráfico
        $cantidades = $contratosPorDia->pluck('cantidad');
        $labels = $dias->map(function ($fecha) {
            return \Carbon\Carbon::parse($fecha)->isoFormat('ddd'); // Ej: Lun, Mar, etc.
        });

        // Agenda de pagos - obtener el offset de día desde la URL
        $dayOffset = $request->input('day', 0);

        // Validar y convertir a entero de forma segura
        if (!is_numeric($dayOffset)) {
            $dayOffset = 0;
        } else {
            $dayOffset = (int) $dayOffset;
            // Limitar el rango para evitar fechas extremas
            $dayOffset = max(-365, min(365, $dayOffset));
        }

        // Obtener el offset de semana para empleados
        $weekOffset = $request->input('week', 0);
        if (!is_numeric($weekOffset)) {
            $weekOffset = 0;
        } else {
            $weekOffset = (int) $weekOffset;
            // Limitar el rango de semanas
            $weekOffset = max(-52, min(52, $weekOffset));
        }

        // Configurar locale en español
        Carbon::setLocale('es');

        // Calcular la fecha específica
        $fecha = Carbon::now()->addDays($dayOffset);

        // Obtener pagos para el día específico
        $pagosPendientes = \App\Models\Contrato::with(['cliente'])
            ->whereDate('proxima_fecha_pago', $fecha->format('Y-m-d'))
            ->where('estado', 'activo')
            ->get()
            ->map(function ($contrato) {
                return $contrato->siguiente_pago_calculado; // Obtiene el objeto pago simulado
            })->filter(); // Elimina los nulos si los hay

        $pagosHechos = \App\Models\Pago::with(['contrato', 'contrato.cliente'])
            ->whereDate('fecha_pago', $fecha->format('Y-m-d'))
            ->where('estado', 'hecho')
            ->get();

        // Crear estructura de datos para el día
        $agendaDia = [
            'fecha' => $fecha,
            'dia_nombre' => ucfirst($fecha->isoFormat('dddd')),
            'dia_numero' => $fecha->format('d'),
            'mes' => ucfirst($fecha->isoFormat('MMM')),
            'pagos_pendientes' => $pagosPendientes,
            'pagos_hechos' => $pagosHechos
        ];

        // Si es una petición AJAX para cambiar semana
        if ($request->ajax() && $request->has('week')) {
            $empleadoAgenda = $this->getEmpleadoAgenda($weekOffset);

            return response()->json([
                'success' => true,
                'empleadoAgenda' => $this->formatEmpleadoAgendaForAjax($empleadoAgenda)
            ]);
        }

        return view('home', [
            'contratosLabels' => $labels,
            'contratosData' => $cantidades,
            'agendaDia' => $agendaDia,
            'currentDayOffset' => $dayOffset,
            'totalPagosDay' => $pagosPendientes->count() + $pagosHechos->count(),
            'empleadoContratos' => $this->getEmpleadoContratos(),
            'empleadoAgenda' => $this->getEmpleadoAgenda($weekOffset),
            'empleadoPagosVencidos' => $this->getEmpleadoPagosVencidos(),
            'empleadoRutas' => $this->getEmpleadoRutas($fecha),
            'empleadoPlantillas' => $this->getEmpleadoPlantillas(),
            'rutasDiaTitulo' => $this->etiquetaDiaCorta($fecha),
            'rutasDiaSubtitulo' => $fecha->isoFormat('D [de] MMMM'),
        ]);
    }

    /**
     * Obtener contratos asignados al empleado del usuario logueado
     */
    private function getEmpleadoContratos()
    {
        $user = Auth::user();

        // Si es admin, no mostrar contratos de empleado
        if ($user->role === 'admin') {
            return collect();
        }

        // Buscar el empleado asociado al usuario
        $empleado = Empleado::where('user_id', $user->id)->first();

        if (!$empleado) {
            return collect();
        }

        // Obtener contratos que tienen comisiones asignadas al empleado
        $contratoIds = \App\Models\Comisione::where('empleado_id', $empleado->id)
            ->pluck('contrato_id')
            ->unique()
            ->filter(); // Filtrar valores null

        return Contrato::with(['cliente', 'paquete'])
            ->whereIn('id', $contratoIds)
            ->where('estado', 'activo')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Obtener agenda de pagos próximos para el empleado del usuario logueado
     */
    private function getEmpleadoAgenda($weekOffset = 0)
    {
        $user = Auth::user();

        // Si es admin, no mostrar agenda de empleado
        if ($user->role === 'admin') {
            return collect();
        }

        // Buscar el empleado asociado al usuario
        $empleado = Empleado::where('user_id', $user->id)->first();

        if (!$empleado) {
            return collect();
        }

        // Obtener IDs de contratos que tienen comisiones asignadas al empleado
        $contratoIds = \App\Models\Comisione::where('empleado_id', $empleado->id)
            ->pluck('contrato_id')
            ->unique()
            ->filter(); // Filtrar valores null

        // Configurar locale en español
        Carbon::setLocale('es');

        // Generar los 7 días basados en el offset de semana
        $agendaDias = collect();
        $fechaInicio = Carbon::now()->addWeeks($weekOffset);

        for ($i = 0; $i < 7; $i++) {
            $fecha = $fechaInicio->copy()->addDays($i);

            // Obtener pagos para contratos del empleado en esta fecha
            $pagosPendientes = \App\Models\Contrato::with(['cliente'])
                ->whereIn('id', $contratoIds)
                ->whereDate('proxima_fecha_pago', $fecha->format('Y-m-d'))
                ->where('estado', 'activo')
                ->get()
                ->map(function ($contrato) {
                    return $contrato->siguiente_pago_calculado; // Obtiene el objeto pago simulado
                })->filter() // Elimina los nulos si los hay
                ->values(); // Resetear indices para que jsonSerialize no lo convierta en objeto json


            $pagosHechos = Pago::with(['contrato', 'contrato.cliente'])
                ->whereIn('contrato_id', $contratoIds)
                ->whereDate('fecha_pago', $fecha->format('Y-m-d'))
                ->where('estado', 'hecho')
                ->get();

            $agendaDias->push([
                'fecha' => $fecha,
                'dia_nombre' => ucfirst($fecha->isoFormat('dddd')),
                'dia_numero' => $fecha->format('d'),
                'mes' => ucfirst($fecha->isoFormat('MMM')),
                'pagos_pendientes' => $pagosPendientes,
                'pagos_hechos' => $pagosHechos
            ]);
        }

        return $agendaDias;
    }

    /**
     * Obtener pagos vencidos para el empleado del usuario logueado
     */
    private function getEmpleadoPagosVencidos()
    {
        $user = Auth::user();

        // Si es admin, no mostrar pagos vencidos de empleado
        if ($user->role === 'admin') {
            return collect();
        }

        // Buscar el empleado asociado al usuario
        $empleado = Empleado::where('user_id', $user->id)->first();

        if (!$empleado) {
            return collect();
        }

        // Obtener IDs de contratos que tienen comisiones asignadas al empleado
        $contratoIds = \App\Models\Comisione::where('empleado_id', $empleado->id)
            ->pluck('contrato_id')
            ->unique()
            ->filter(); // Filtrar valores null

        // Configurar locale en español
        Carbon::setLocale('es');

        // Obtener la tolerancia de pagos desde los ajustes
        $toleranciaDias = \App\Models\Ajuste::obtenerToleranciaPagos();

        // Calcular la fecha límite considerando la tolerancia
        $fechaLimite = Carbon::now()->subDays($toleranciaDias)->endOfDay();

        // Obtener pagos vencidos (pendientes que son anteriores a la fecha límite)
        $pagosVencidos = Pago::with(['contrato', 'contrato.cliente'])
            ->whereIn('contrato_id', $contratoIds)
            ->where('fecha_pago', '<', $fechaLimite)
            ->where('estado', 'pendiente')
            ->orderBy('fecha_pago', 'desc')
            ->get();

        return $pagosVencidos;
    }

    /**
     * Formatear la agenda del empleado para respuesta AJAX
     */
    private function formatEmpleadoAgendaForAjax($empleadoAgenda)
    {
        return $empleadoAgenda->map(function ($dia) {
            $pagosPendientes = $dia['pagos_pendientes'] ?? collect();
            $pagosHechos = $dia['pagos_hechos'] ?? collect();

            // Combinar pagos pendientes y hechos para el formato del JS
            $todosPagos = $pagosPendientes->merge($pagosHechos)->map(function ($pago) {
                return [
                    'id' => $pago->id,
                    'monto' => $pago->monto,
                    'cliente_nombre' => $pago->contrato->cliente->nombre ?? 'Cliente desconocido',
                    'contrato_id' => $pago->contrato_id,
                    'estado' => $pago->estado
                ];
            });

            return [
                'fecha' => $dia['fecha']->format('Y-m-d'),
                'dia_nombre' => $dia['dia_nombre'],
                'dia_numero' => $dia['dia_numero'],
                'mes' => $dia['mes'],
                'pagos' => $todosPagos,
                'pagos_pendientes_count' => $pagosPendientes->count(),
                'pagos_hechos_count' => $pagosHechos->count()
            ];
        });
    }

    public function rutasDia(Request $request)
    {
        $dayOffset = $request->input('day', 0);
        if (! is_numeric($dayOffset)) {
            $dayOffset = 0;
        } else {
            $dayOffset = (int) $dayOffset;
            $dayOffset = max(-365, min(365, $dayOffset));
        }

        Carbon::setLocale('es');
        $fecha = Carbon::now()->addDays($dayOffset)->startOfDay();
        $rutas = $this->getEmpleadoRutas($fecha);

        return response()->json([
            'success' => true,
            'offset' => $dayOffset,
            'titulo' => $this->etiquetaDiaCorta($fecha),
            'subtitulo' => $fecha->isoFormat('D [de] MMMM'),
            'rutas' => $rutas->map(fn (Ruta $ruta) => $this->serializarRutaEmpleado($ruta))->values(),
        ]);
    }

    private function etiquetaDiaCorta(Carbon $fecha): string
    {
        if ($fecha->isToday()) {
            return 'Hoy';
        }
        if ($fecha->isYesterday()) {
            return 'Ayer';
        }
        if ($fecha->isTomorrow()) {
            return 'Mañana';
        }

        return ucfirst($fecha->isoFormat('dddd'));
    }

    private function getEmpleadoPlantillas()
    {
        $user = Auth::user();
        if ($user->role === 'admin') {
            return collect();
        }

        $empleado = Empleado::where('user_id', $user->id)->first();
        if (! $empleado) {
            return collect();
        }

        return RutaPlantilla::withCount('paradas')
            ->where('empleado_id', $empleado->id)
            ->where('activa', true)
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Instancias del empleado para un día.
     */
    private function getEmpleadoRutas(?Carbon $fecha = null)
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return collect();
        }

        $empleado = Empleado::where('user_id', $user->id)->first();

        if (! $empleado) {
            return collect();
        }

        $fecha = ($fecha ?? Carbon::today())->toDateString();

        return Ruta::with(['paradas.contrato.cliente', 'empleado', 'plantilla'])
            ->where('empleado_id', $empleado->id)
            ->whereDate('fecha', $fecha)
            ->where('estado', '!=', Ruta::ESTADO_CANCELADA)
            ->orderBy('nombre')
            ->orderBy('id')
            ->get();
    }

    private function serializarRutaEmpleado(Ruta $ruta): array
    {
        $domiciliosUnicos = [];
        foreach ($ruta->paradas as $parada) {
            $dkey = $parada->cliente_id . '_' . md5($parada->direccion_destino ?? '');
            if (! isset($domiciliosUnicos[$dkey])) {
                $domiciliosUnicos[$dkey] = ['paradas' => collect()];
            }
            $domiciliosUnicos[$dkey]['paradas']->push($parada);
        }
        $totalParadas = count($domiciliosUnicos);
        $completadas = 0;
        $omitidas = 0;
        foreach ($domiciliosUnicos as $d) {
            $t = $d['paradas']->count();
            $c = $d['paradas']->where('estado', 'visitada')->count();
            $o = $d['paradas']->where('estado', 'omitida')->count();
            if ($c === $t) {
                $completadas++;
            } elseif ($o === $t) {
                $omitidas++;
            }
        }
        $porcentaje = $totalParadas > 0 ? (int) round(($completadas / $totalParadas) * 100) : 0;
        $estadoConfig = match ($ruta->estado) {
            'planeada' => ['color' => '#007AFF', 'bg' => 'rgba(0,122,255,0.1)', 'label' => 'Planeada', 'icon' => 'bi-calendar'],
            'en_curso' => ['color' => '#FF9500', 'bg' => 'rgba(255,149,0,0.1)', 'label' => 'En Curso', 'icon' => 'bi-play-circle'],
            'completada' => ['color' => '#34C759', 'bg' => 'rgba(52,199,89,0.1)', 'label' => 'Completada', 'icon' => 'bi-check-circle'],
            'incompleta' => ['color' => '#8E8E93', 'bg' => 'rgba(142,142,147,0.1)', 'label' => 'Incompleta', 'icon' => 'bi-dash-circle'],
            'vencida' => ['color' => '#1C1C1E', 'bg' => 'rgba(28,28,30,0.1)', 'label' => 'Vencida', 'icon' => 'bi-clock-history'],
            default => ['color' => '#8E8E93', 'bg' => 'rgba(142,142,147,0.1)', 'label' => $ruta->estado, 'icon' => 'bi-circle'],
        };

        return [
            'id' => $ruta->id,
            'nombre' => $ruta->nombre ?: ('Ruta #' . $ruta->id),
            'estado' => $ruta->estado,
            'estado_label' => $estadoConfig['label'],
            'estado_color' => $estadoConfig['color'],
            'estado_bg' => $estadoConfig['bg'],
            'estado_icon' => $estadoConfig['icon'],
            'total_domicilios' => $totalParadas,
            'completadas' => $completadas,
            'omitidas' => $omitidas,
            'porcentaje' => $porcentaje,
        ];
    }
}
