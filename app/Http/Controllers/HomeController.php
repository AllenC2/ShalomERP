<?php

namespace App\Http\Controllers;


use App\Models\Comisione;
use App\Models\Contrato;
use App\Models\Empleado;
use App\Models\Pago;
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
        $labels = $dias->map(function($fecha) {
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
        $pagosPendientes = \App\Models\Pago::with(['contrato', 'contrato.cliente'])
            ->whereDate('fecha_pago', $fecha->format('Y-m-d'))
            ->where('estado', 'pendiente')
            ->get();
            
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
            'empleadoPagosVencidos' => $this->getEmpleadoPagosVencidos()
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
            $pagosPendientes = Pago::with(['contrato', 'contrato.cliente'])
                ->whereIn('contrato_id', $contratoIds)
                ->whereDate('fecha_pago', $fecha->format('Y-m-d'))
                ->where('estado', 'pendiente')
                ->get();
                
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
}
