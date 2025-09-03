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

        // Agenda de pagos - obtener el offset de semana desde la URL
        $weekOffset = $request->input('week', 0);
        
        // Validar y convertir a entero de forma segura
        if (!is_numeric($weekOffset)) {
            $weekOffset = 0;
        } else {
            $weekOffset = (int) $weekOffset;
            // Limitar el rango para evitar fechas extremas
            $weekOffset = max(-52, min(52, $weekOffset));
        }
        
        // Configurar locale en español
        Carbon::setLocale('es');
        
        // Calcular la fecha de inicio de la semana
        $startDate = Carbon::now()->addWeeks($weekOffset)->startOfWeek(Carbon::MONDAY);
        
        // Generar 7 días consecutivos empezando desde startDate
        $agendaDias = collect();
        $totalPagosSemana = 0;
        
        for ($i = 0; $i < 7; $i++) {
            $fecha = $startDate->copy()->addDays($i);
            $pagosPendientes = \App\Models\Pago::with(['contrato', 'contrato.cliente'])
                ->whereDate('fecha_pago', $fecha->format('Y-m-d'))
                ->where('estado', 'pendiente')
                ->get();
                
            $pagosHechos = \App\Models\Pago::with(['contrato', 'contrato.cliente'])
                ->whereDate('fecha_pago', $fecha->format('Y-m-d'))
                ->where('estado', 'hecho')
                ->get();
                
            $totalPagosSemana += $pagosPendientes->count();
            
            $agendaDias->push([
                'fecha' => $fecha,
                'dia_nombre' => ucfirst($fecha->isoFormat('dddd')),
                'dia_numero' => $fecha->format('d'),
                'mes' => ucfirst($fecha->isoFormat('MMM')),
                'pagos_pendientes' => $pagosPendientes,
                'pagos_hechos' => $pagosHechos
            ]);
        }

        return view('home', [
            'contratosLabels' => $labels,
            'contratosData' => $cantidades,
            'agendaDias' => $agendaDias,
            'currentWeekOffset' => $weekOffset,
            'totalPagosSemana' => $totalPagosSemana,
            'empleadoContratos' => $this->getEmpleadoContratos(),
            'empleadoAgenda' => $this->getEmpleadoAgenda(),
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
    private function getEmpleadoAgenda()
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
        
        // Generar los próximos 7 días
        $agendaDias = collect();
        $hoy = Carbon::now();
        
        for ($i = 0; $i < 7; $i++) {
            $fecha = $hoy->copy()->addDays($i);
            
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
}
