<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\Cliente;
use App\Models\Pago;
use App\Models\Comisione;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\ContratoRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ContratoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $soloActivos = $request->input('solo_activos', '1'); // Por defecto activado
        $contratosQuery = Contrato::query();
        if ($search) {
            $contratosQuery = $contratosQuery->whereHas('cliente', function($q) use ($search) {
                $q->where('nombre', 'like', "%$search%")
                  ->orWhere('apellido', 'like', "%$search%");
            });
        }
        if ($soloActivos) {
            $contratosQuery = $contratosQuery->where('estado', 'Activo');
        }
        $contratos = $contratosQuery->paginate();
        // Calcular porcentaje pagado para cada contrato
        foreach ($contratos as $contrato) {
            $pagado = Pago::where('contrato_id', $contrato->id)
                ->where('estado', 'Hecho')
                ->sum('monto');
            $total = $contrato->monto_total ?? 0;
            $contrato->porcentaje_pagado = $total > 0 ? round(($pagado / $total) * 100, 2) : 0;
        }

        return view('contrato.index', compact('contratos'))
            ->with('i', ($request->input('page', 1) - 1) * $contratos->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $contrato = new Contrato();

        $clientes = \App\Models\Cliente::selectRaw("CONCAT(nombre, ' ', apellido) as nombre_completo, id")
            ->pluck('nombre_completo', 'id');
        $empleados = \App\Models\Empleado::selectRaw("CONCAT(nombre, ' ', apellido) as nombre_completo, id")
            ->pluck('nombre_completo', 'id');
        $paquetes = \App\Models\Paquete::pluck('nombre', 'id');

        return view('contrato.create', compact('contrato', 'clientes', 'empleados', 'paquetes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ContratoRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Limpiar formato de monto_inicial (eliminar comas y convertir a float)
        if (isset($data['monto_inicial'])) {
            $data['monto_inicial'] = floatval(str_replace(',', '', $data['monto_inicial']));
        } else {
            $data['monto_inicial'] = 0;
        }

        // Obtener el precio del paquete seleccionado
        $paquete = \App\Models\Paquete::find($data['paquete_id']);
        $data['monto_total'] = $paquete ? $paquete->precio : 0;
        $montoTotal = $data['monto_total'];

        // Guardar el monto inicial tal como se escribió
        $montoInicial = isset($data['monto_inicial']) ? floatval($data['monto_inicial']) : 0;
        $data['monto_inicial'] = $montoInicial;

        $contrato = Contrato::create($data);

        // Crear pago inicial si existe monto_inicial
        if ($montoInicial > 0) {
            \App\Models\Pago::create([
                'contrato_id' => $contrato->id,
                'monto' => $montoInicial,
                'estado' => 'Hecho',
                'observaciones' => 'Pago inicial',
                'metodo_pago' => 'Pendiente',
                'fecha_pago' => $contrato->fecha_inicio ? \Carbon\Carbon::parse($contrato->fecha_inicio) : now(),
            ]);
        }

        // Calcular monto restante para pagos futuros
        $montoRestante = max($montoTotal - $montoInicial, 0);

        // Crear pagos automáticos según plazo_cantidad
        $cantidadPagos = (int)($contrato->plazo_cantidad ?? 0);
        $plazoTipo = strtolower($contrato->plazo_tipo ?? '');
        $plazoFrecuencia = (int)($contrato->plazo_frencuencia ?? 1);
        $fechaInicio = $contrato->fecha_inicio ? \Carbon\Carbon::parse($contrato->fecha_inicio) : now();
        if ($cantidadPagos > 0 && $montoRestante > 0) {
            $montoPorPago = round($montoRestante / max($cantidadPagos, 1), 2);
            for ($i = 1; $i <= $cantidadPagos; $i++) {
                // Calcular fecha de pago, asegurando que sea después de fecha_inicio
                if ($plazoTipo === 'mensual') {
                    // El primer pago es al siguiente mes de fecha_inicio
                    $fechaPago = $fechaInicio->copy()->addMonths($i)->day($plazoFrecuencia);
                    if ($fechaPago->month !== $fechaInicio->copy()->addMonths($i)->month) {
                        $fechaPago = $fechaInicio->copy()->addMonths($i)->endOfMonth();
                    }
                } elseif ($plazoTipo === 'semanal') {
                    // El primer pago es a la siguiente semana de fecha_inicio
                    $fechaPago = $fechaInicio->copy()->addWeeks($i)->next($plazoFrecuencia);
                } else {
                    // Por defecto, pagos consecutivos desde el día siguiente a fecha_inicio
                    $fechaPago = $fechaInicio->copy()->addDays($i);
                }
                // Ajustar el último pago para cuadrar el monto exacto
                if ($i == $cantidadPagos) {
                    $monto = round($montoRestante - ($montoPorPago * ($cantidadPagos - 1)), 2);
                } else {
                    $monto = $montoPorPago;
                }
                \App\Models\Pago::create([
                    'contrato_id' => $contrato->id,
                    'monto' => $monto,
                    'estado' => 'Pendiente',
                    'observaciones' => 'Pago generado automaticamente (' . $i . '/' . $cantidadPagos . ')',
                    'metodo_pago' => 'Pendiente',
                    'fecha_pago' => $fechaPago,
                ]);
            }
        }

        return Redirect::route('contratos.index')
            ->with('success', 'Contrato created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $contrato = Contrato::find($id);

        $pagos_contrato = Pago::where('contrato_id', $id)->get();

        return view('contrato.show', compact('contrato', 'pagos_contrato'));
    }

    /**
     * Display the comisiones for a specific contrato.
     */
    public function comisiones($id): View
    {
        $contrato = Contrato::findOrFail($id);
        $comisiones = Comisione::where('contrato_id', $id)->paginate();

        return view('contrato.comisiones', compact('contrato', 'comisiones'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $contrato = Contrato::find($id);
        
        $clientes = \App\Models\Cliente::selectRaw("CONCAT(nombre, ' ', apellido) as nombre_completo, id")
            ->pluck('nombre_completo', 'id');
        $clienteName = Cliente::find($contrato->cliente_id)?->name;
        $contrato->cliente_id = $clienteName ?? $contrato->cliente_id;

        $empleados = \App\Models\Empleado::selectRaw("CONCAT(nombre, ' ', apellido) as nombre_completo, id")
            ->pluck('nombre_completo', 'id');
        $empleadoName = \App\Models\Empleado::find($contrato->empleado_id)?->name;
        $contrato->empleado_id = $empleadoName ?? $contrato->empleado_id;

        $paquetes = \App\Models\Paquete::pluck('nombre', 'id');
        $paqueteName = \App\Models\Paquete::find($contrato->paquete_id)?->name;
        $contrato->paquete_id = $paqueteName ?? $contrato->paquete_id;

        return view('contrato.edit', compact('contrato','clientes', 'empleados', 'paquetes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ContratoRequest $request, Contrato $contrato): RedirectResponse
    {
        $data = $request->validated();

        // Limpiar formato de monto_inicial (eliminar comas y convertir a float)
        if (isset($data['monto_inicial'])) {
            $data['monto_inicial'] = floatval(str_replace(',', '', $data['monto_inicial']));
        }

        // Si el paquete fue actualizado, obtener el precio del nuevo paquete
        if (isset($data['paquete_id']) && $data['paquete_id'] != $contrato->paquete_id) {
            $paquete = \App\Models\Paquete::find($data['paquete_id']);
            $data['monto_total'] = $paquete ? $paquete->precio : 0;
        }

        $contrato->update($data);

        return Redirect::route('contratos.index')
            ->with('success', 'Contrato updated successfully');
    }

    public function destroy($id): RedirectResponse
    {
        Contrato::find($id)->delete();

        return Redirect::route('contratos.index')
            ->with('success', 'Contrato deleted successfully');
    }

    public function cancel($id): RedirectResponse
    {
        $contrato = Contrato::findOrFail($id);
        $contrato->estado = 'Cancelado';
        $contrato->save();
        return Redirect::route('contratos.index')
            ->with('success', 'Contrato cancelado correctamente.');
    }
}
