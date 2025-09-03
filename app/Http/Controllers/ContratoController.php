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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
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
        
        // Debug: Log de valores recibidos
        \Log::info('Filtros recibidos:', [
            'search' => $search,
            'solo_activos' => $soloActivos,
            'is_ajax' => $request->hasHeader('X-Requested-With')
        ]);
        
        $contratosQuery = Contrato::with(['cliente', 'paquete', 'pagos']);
        
        if ($search) {
            $contratosQuery = $contratosQuery->whereHas('cliente', function($q) use ($search) {
                $q->where('nombre', 'like', "%$search%")
                  ->orWhere('apellido', 'like', "%$search%");
            });
        }
        
        if ($soloActivos === '1') {
            $contratosQuery = $contratosQuery->where('estado', 'activo');
        }
        
        $contratos = $contratosQuery->paginate();
        
        // Calcular porcentaje pagado y información de cuotas para cada contrato
        foreach ($contratos as $contrato) {
            $pagado = Pago::where('contrato_id', $contrato->id)
                ->where('estado', 'hecho')
                ->sum('monto');
            $total = $contrato->monto_total ?? 0;
            $contrato->porcentaje_pagado = $total > 0 ? round(($pagado / $total) * 100, 2) : 0;
            
            // Agregar información de cuotas vencidas y pendientes
            $contrato->estado_pagos = $contrato->estado_pagos;
        }

        // Si es una petición AJAX, devolver solo la vista parcial
        if ($request->hasHeader('X-Requested-With') && $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return view('contrato.partials.table', compact('contratos'));
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
        $paquetes = \App\Models\Paquete::pluck('nombre', 'id');

        return view('contrato.create', compact('contrato', 'clientes', 'paquetes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ContratoRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Manejar subida de archivo PDF
        if ($request->hasFile('documento')) {
            $file = $request->file('documento');
            
            // Validar que sea un PDF
            if ($file->getClientMimeType() !== 'application/pdf') {
                return back()->withErrors(['documento' => 'El archivo debe ser un PDF válido.'])->withInput();
            }
            
            // Generar nombre único para el archivo
            $fileName = 'contrato_' . time() . '_' . uniqid() . '.pdf';
            
            // Guardar el archivo en storage/app/public/contratos
            $filePath = $file->storeAs('contratos', $fileName, 'public');
            
            // Guardar la ruta en la base de datos
            $data['documento'] = $filePath;
        } else {
            $data['documento'] = 'No';
        }

        // Los montos ya vienen limpios desde ContratoRequest::prepareForValidation()
        $data['monto_inicial'] = $data['monto_inicial'] ?? 0;
        $data['monto_bonificacion'] = $data['monto_bonificacion'] ?? 0;
        $data['monto_cuota'] = $data['monto_cuota'] ?? 0;

        // Obtener el precio del paquete seleccionado
        $paquete = \App\Models\Paquete::find($data['paquete_id']);
        $data['monto_total'] = $paquete ? $paquete->precio : 0;
        $montoTotal = $data['monto_total'];
        $montoInicial = $data['monto_inicial'];
        $montoBonificacion = $data['monto_bonificacion'];

        // Calcular fecha_fin basada en numero_cuotas y frecuencia_cuotas
        $numeroCuotas = (int)($data['numero_cuotas'] ?? 0);
        $frecuenciaCuotas = (int)($data['frecuencia_cuotas'] ?? 7);
        $fechaInicio = $data['fecha_inicio'] ? \Carbon\Carbon::parse($data['fecha_inicio']) : now();
        
        if ($numeroCuotas > 0 && $frecuenciaCuotas > 0) {
            $data['fecha_fin'] = $fechaInicio->copy()->addDays($frecuenciaCuotas * $numeroCuotas)->format('Y-m-d');
        } else {
            $data['fecha_fin'] = $fechaInicio->copy()->addYear()->format('Y-m-d'); // Por defecto 1 año
        }

        $contrato = Contrato::create($data);

        // Crear pago inicial si existe monto_inicial
        if ($montoInicial > 0) {
            \App\Models\Pago::create([
                'contrato_id' => $contrato->id,
                'tipo_pago' => 'inicial',
                'metodo_pago' => 'otro',
                'monto' => $montoInicial,
                'fecha_pago' => $contrato->fecha_inicio ? \Carbon\Carbon::parse($contrato->fecha_inicio) : now(),
                'referencia' => null,
                'saldo_restante' => $montoTotal - $montoInicial,
                'documento' => null,
                'observaciones' => 'Pago inicial',
                'estado' => 'hecho',
            ]);
        }

        // Crear pago de bonificación si existe monto_bonificacion
        if ($montoBonificacion > 0) {
            $saldoRestanteDespuesInicial = $montoTotal - $montoInicial;
            \App\Models\Pago::create([
                'contrato_id' => $contrato->id,
                'tipo_pago' => 'bonificación',
                'metodo_pago' => 'otro',
                'monto' => $montoBonificacion,
                'fecha_pago' => $contrato->fecha_inicio ? \Carbon\Carbon::parse($contrato->fecha_inicio) : now(),
                'referencia' => null,
                'saldo_restante' => $saldoRestanteDespuesInicial - $montoBonificacion,
                'documento' => null,
                'observaciones' => 'Bonificación aplicada',
                'estado' => 'hecho',
            ]);
        }

        // Calcular saldo pendiente para pagos futuros
        $saldoPendiente = max($montoTotal - $montoInicial - $montoBonificacion, 0);

        // Crear pagos automáticos según numero_cuotas
        $numeroCuotas = (int)($contrato->numero_cuotas ?? 0);
        $frecuenciaCuotas = (int)($contrato->frecuencia_cuotas ?? 7);
        $fechaInicio = $contrato->fecha_inicio ? \Carbon\Carbon::parse($contrato->fecha_inicio) : now();
        
        if ($numeroCuotas > 0 && $saldoPendiente > 0) {
            // Calcular distribución exacta de pagos
            $montoPorCuotaBase = floor(($saldoPendiente / $numeroCuotas) * 100) / 100;
            $totalCuotasBase = $montoPorCuotaBase * $numeroCuotas;
            $ajusteUltimaCuota = round($saldoPendiente - $totalCuotasBase, 2);
            
            $saldoActual = $saldoPendiente;
            
            for ($i = 1; $i <= $numeroCuotas; $i++) {
                // Calcular fecha de pago basada en frecuencia de días
                $fechaPago = $fechaInicio->copy()->addDays($frecuenciaCuotas * $i);
                
                // Determinar el monto de esta cuota
                if ($i == $numeroCuotas) {
                    // La última cuota incluye cualquier ajuste por decimales
                    $monto = $montoPorCuotaBase + $ajusteUltimaCuota;
                } else {
                    $monto = $montoPorCuotaBase;
                }
                
                // Asegurar que no hay montos negativos
                $monto = max($monto, 0);
                
                // Calcular saldo restante después de este pago
                $saldoRestanteDespuesPago = max($saldoActual - $monto, 0);
                
                // Generar observaciones descriptivas
                $observaciones = 'Cuota ' . $i . ' de ' . $numeroCuotas;
                if ($i == $numeroCuotas && abs($ajusteUltimaCuota) > 0.01) {
                    $observaciones .= ($ajusteUltimaCuota > 0 ? ' (Incluye ajuste +$' : ' (Incluye ajuste -$') 
                                    . number_format(abs($ajusteUltimaCuota), 2) . ' por decimales)';
                }
                
                \App\Models\Pago::create([
                    'contrato_id' => $contrato->id,
                    'tipo_pago' => 'cuota',
                    'metodo_pago' => 'otro',
                    'monto' => round($monto, 2),
                    'fecha_pago' => $fechaPago,
                    'numero_cuota' => $i, // Asignar el número de cuota que coincide con las observaciones
                    'referencia' => null,
                    'saldo_restante' => round($saldoRestanteDespuesPago, 2),
                    'documento' => null,
                    'observaciones' => $observaciones,
                    'estado' => 'pendiente',
                ]);
                
                // Actualizar saldo actual para la siguiente iteración
                $saldoActual = $saldoRestanteDespuesPago;
            }
        }

        // Crear comisiones si se proporcionaron
        if ($request->has('comisiones') && is_array($request->comisiones)) {
            foreach ($request->comisiones as $porcentaje_id => $empleado_id) {
                if (!empty($empleado_id)) {
                    $porcentaje = \App\Models\Porcentaje::find($porcentaje_id);
                    if ($porcentaje) {
                        $montoComision = ($montoTotal * $porcentaje->cantidad_porcentaje) / 100;
                        
                        \App\Models\Comisione::create([
                            'contrato_id' => $contrato->id,
                            'nombre_paquete' => $contrato->paquete->nombre,
                            'empleado_id' => $empleado_id,
                            'fecha_comision' => $contrato->fecha_inicio ?? now(),
                            'porcentaje' => $porcentaje->cantidad_porcentaje,
                            'tipo_comision' => $porcentaje->tipo_porcentaje,
                            'monto' => $montoComision,
                            'observaciones' => "Comisión por {$porcentaje->tipo_porcentaje} ({$porcentaje->cantidad_porcentaje}%)",
                            'documento' => 'No',
                            'estado' => 'Pendiente'
                        ]);
                    }
                }
            }
        }

        return Redirect::route('contratos.show', $contrato->id)
            ->with('success', 'Contrato creado correctamente..');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $contrato = Contrato::with(['cliente', 'paquete'])->find($id);

        $pagos_contrato = Pago::where('contrato_id', $id)->get();
        
        // Obtener información adicional del cliente
        $cliente = $contrato->cliente;
        $contratos_cliente = Contrato::where('cliente_id', $cliente->id)->count();
        $contratos_activos_cliente = Contrato::where('cliente_id', $cliente->id)
            ->where('estado', 'activo')
            ->count();

        return view('contrato.show', compact('contrato', 'pagos_contrato', 'contratos_cliente', 'contratos_activos_cliente'));
    }

    /**
     * Display the comisiones for a specific contrato.
     */
    public function comisiones($id): View
    {
        $contrato = Contrato::findOrFail($id);
        // Obtener todas las comisiones (padres e hijas) para mostrar en la tabla, ordenadas por nombre del empleado
        $comisiones = Comisione::where('contrato_id', $id)
            ->with(['empleado', 'comisionPadre', 'parcialidades'])
            ->join('empleados', 'comisiones.empleado_id', '=', 'empleados.id')
            ->orderBy('empleados.nombre', 'asc')
            ->orderBy('empleados.apellido', 'asc')
            ->select('comisiones.*')
            ->get();
        
        // Obtener solo comisiones padre para el formulario de parcialidades
        $comisionesPadre = Comisione::where('contrato_id', $id)
            ->whereNull('comision_padre_id')
            ->with(['empleado', 'parcialidades'])
            ->join('empleados', 'comisiones.empleado_id', '=', 'empleados.id')
            ->orderBy('empleados.nombre', 'asc')
            ->orderBy('empleados.apellido', 'asc')
            ->select('comisiones.*')
            ->get();

        // Calcular la suma de pagos con estado "hecho"
        $totalPagosHechos = \App\Models\Pago::where('contrato_id', $id)
            ->where('estado', 'hecho')
            ->sum('monto');

        return view('contrato.comisiones', compact('contrato', 'comisiones', 'comisionesPadre', 'totalPagosHechos'));
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

        $paquetes = \App\Models\Paquete::pluck('nombre', 'id');
        $paqueteName = \App\Models\Paquete::find($contrato->paquete_id)?->name;
        $contrato->paquete_id = $paqueteName ?? $contrato->paquete_id;

        // Cargar comisiones existentes
        $comisionesExistentes = \App\Models\Comisione::where('contrato_id', $contrato->id)
            ->with('empleado')
            ->get()
            ->keyBy('tipo_comision');

        return view('contrato.edit', compact('contrato','clientes', 'paquetes', 'comisionesExistentes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ContratoRequest $request, Contrato $contrato): RedirectResponse
    {
        $data = $request->validated();

        // Manejar subida de archivo PDF
        if ($request->hasFile('documento')) {
            $file = $request->file('documento');
            
            // Validar que sea un PDF
            if ($file->getClientMimeType() !== 'application/pdf') {
                return back()->withErrors(['documento' => 'El archivo debe ser un PDF válido.'])->withInput();
            }
            
            // Eliminar archivo anterior si existe
            if ($contrato->documento && $contrato->documento !== 'No' && \Storage::disk('public')->exists($contrato->documento)) {
                \Storage::disk('public')->delete($contrato->documento);
            }
            
            // Generar nombre único para el archivo
            $fileName = 'contrato_' . $contrato->id . '_' . time() . '_' . uniqid() . '.pdf';
            
            // Guardar el archivo en storage/app/public/contratos
            $filePath = $file->storeAs('contratos', $fileName, 'public');
            
            // Guardar la ruta en los datos
            $data['documento'] = $filePath;
        }

        // Los montos ya vienen limpios desde ContratoRequest::prepareForValidation()
        // Solo necesitamos asegurar valores por defecto
        if (isset($data['monto_inicial'])) {
            $data['monto_inicial'] = $data['monto_inicial'] ?? 0;
        }

        if (isset($data['monto_bonificacion'])) {
            $data['monto_bonificacion'] = $data['monto_bonificacion'] ?? 0;
        }

        if (isset($data['monto_cuota'])) {
            $data['monto_cuota'] = $data['monto_cuota'] ?? 0;
        }

        // Si el paquete fue actualizado, obtener el precio del nuevo paquete
        if (isset($data['paquete_id']) && $data['paquete_id'] != $contrato->paquete_id) {
            $paquete = \App\Models\Paquete::find($data['paquete_id']);
            $data['monto_total'] = $paquete ? $paquete->precio : 0;
        }

        // Calcular fecha_fin basada en numero_cuotas y frecuencia_cuotas
        if (isset($data['numero_cuotas']) && isset($data['frecuencia_cuotas']) && isset($data['fecha_inicio'])) {
            $numeroCuotas = (int)($data['numero_cuotas'] ?? 0);
            $frecuenciaCuotas = (int)($data['frecuencia_cuotas'] ?? 7);
            $fechaInicio = $data['fecha_inicio'] ? \Carbon\Carbon::parse($data['fecha_inicio']) : \Carbon\Carbon::parse($contrato->fecha_inicio);
            
            if ($numeroCuotas > 0 && $frecuenciaCuotas > 0) {
                $data['fecha_fin'] = $fechaInicio->copy()->addDays($frecuenciaCuotas * $numeroCuotas)->format('Y-m-d');
            }
        }

        $contrato->update($data);

        // Regenerar pagos automáticos si se modificaron los parámetros del contrato
        if (isset($data['numero_cuotas']) || isset($data['frecuencia_cuotas']) || isset($data['monto_inicial']) || isset($data['monto_bonificacion']) || isset($data['fecha_inicio'])) {
            // Eliminar solo cuotas pendientes, conservar pagos Inicial, Bonificacion y Abono que ya estén "Hecho"
            \App\Models\Pago::where('contrato_id', $contrato->id)
                ->where('tipo_pago', 'cuota')
                ->where('estado', 'pendiente')
                ->delete();
            
            // Recalcular y crear nuevos pagos
            $montoTotal = $contrato->monto_total;
            $montoInicial = $contrato->monto_inicial ?? 0;
            $montoBonificacion = $contrato->monto_bonificacion ?? 0;
            
            // Verificar si ya existe un pago inicial
            $pagoInicialExiste = \App\Models\Pago::where('contrato_id', $contrato->id)
                ->where('tipo_pago', 'inicial')
                ->exists();
            
            // Verificar si ya existe un pago de bonificación
            $pagoBonificacionExiste = \App\Models\Pago::where('contrato_id', $contrato->id)
                ->where('tipo_pago', 'bonificación')
                ->exists();
            
            // Crear pago inicial si no existe y hay monto inicial
            if (!$pagoInicialExiste && $montoInicial > 0) {
                \App\Models\Pago::create([
                    'contrato_id' => $contrato->id,
                    'tipo_pago' => 'inicial',
                    'metodo_pago' => 'otro',
                    'monto' => $montoInicial,
                    'fecha_pago' => $contrato->fecha_inicio ? \Carbon\Carbon::parse($contrato->fecha_inicio) : now(),
                    'referencia' => null,
                    'saldo_restante' => $montoTotal - $montoInicial,
                    'documento' => null,
                    'observaciones' => 'Pago inicial',
                    'estado' => 'hecho',
                ]);
            }
            
            // Crear pago de bonificación si no existe y hay monto bonificación
            if (!$pagoBonificacionExiste && $montoBonificacion > 0) {
                $saldoRestanteDespuesInicial = $montoTotal - $montoInicial;
                \App\Models\Pago::create([
                    'contrato_id' => $contrato->id,
                    'tipo_pago' => 'bonificación',
                    'metodo_pago' => 'otro',
                    'monto' => $montoBonificacion,
                    'fecha_pago' => $contrato->fecha_inicio ? \Carbon\Carbon::parse($contrato->fecha_inicio) : now(),
                    'referencia' => null,
                    'saldo_restante' => $saldoRestanteDespuesInicial - $montoBonificacion,
                    'documento' => null,
                    'observaciones' => 'Bonificación aplicada',
                    'estado' => 'hecho',
                ]);
            }
            
            // Calcular saldo pendiente para pagos futuros
            $saldoPendiente = max($montoTotal - $montoInicial - $montoBonificacion, 0);
            
            // Crear pagos automáticos según numero_cuotas
            $numeroCuotas = (int)($contrato->numero_cuotas ?? 0);
            $frecuenciaCuotas = (int)($contrato->frecuencia_cuotas ?? 7);
            $fechaInicio = $contrato->fecha_inicio ? \Carbon\Carbon::parse($contrato->fecha_inicio) : now();
            
            if ($numeroCuotas > 0 && $saldoPendiente > 0) {
                // Calcular distribución exacta de pagos
                $montoPorCuotaBase = floor(($saldoPendiente / $numeroCuotas) * 100) / 100;
                $totalCuotasBase = $montoPorCuotaBase * $numeroCuotas;
                $ajusteUltimaCuota = round($saldoPendiente - $totalCuotasBase, 2);
                
                $saldoActual = $saldoPendiente;
                
                for ($i = 1; $i <= $numeroCuotas; $i++) {
                    // Calcular fecha de pago basada en frecuencia de días
                    $fechaPago = $fechaInicio->copy()->addDays($frecuenciaCuotas * $i);
                    
                    // Determinar el monto de esta cuota
                    if ($i == $numeroCuotas) {
                        // La última cuota incluye cualquier ajuste por decimales
                        $monto = $montoPorCuotaBase + $ajusteUltimaCuota;
                    } else {
                        $monto = $montoPorCuotaBase;
                    }
                    
                    // Asegurar que no hay montos negativos
                    $monto = max($monto, 0);
                    
                    // Calcular saldo restante después de este pago
                    $saldoRestanteDespuesPago = max($saldoActual - $monto, 0);
                    
                    // Generar observaciones descriptivas
                    $observaciones = 'Cuota ' . $i . ' de ' . $numeroCuotas;
                    if ($i == $numeroCuotas && abs($ajusteUltimaCuota) > 0.01) {
                        $observaciones .= ($ajusteUltimaCuota > 0 ? ' (Incluye ajuste +$' : ' (Incluye ajuste -$') 
                                        . number_format(abs($ajusteUltimaCuota), 2) . ' por decimales)';
                    }
                    
                    \App\Models\Pago::create([
                        'contrato_id' => $contrato->id,
                        'tipo_pago' => 'cuota',
                        'metodo_pago' => 'otro',
                        'monto' => round($monto, 2),
                        'fecha_pago' => $fechaPago,
                        'numero_cuota' => $i, // Asignar el número de cuota que coincide con las observaciones
                        'referencia' => null,
                        'saldo_restante' => round($saldoRestanteDespuesPago, 2),
                        'documento' => null,
                        'observaciones' => $observaciones,
                        'estado' => 'pendiente',
                    ]);
                    
                    // Actualizar saldo actual para la siguiente iteración
                    $saldoActual = $saldoRestanteDespuesPago;
                }
            }
        }

        // Actualizar comisiones si se proporcionaron
        if ($request->has('comisiones') && is_array($request->comisiones)) {
            // Eliminar comisiones existentes para este contrato
            \App\Models\Comisione::where('contrato_id', $contrato->id)->delete();
            
            // Crear nuevas comisiones
            $montoTotal = $contrato->monto_total;
            foreach ($request->comisiones as $porcentaje_id => $empleado_id) {
                if (!empty($empleado_id)) {
                    $porcentaje = \App\Models\Porcentaje::find($porcentaje_id);
                    if ($porcentaje) {
                        $montoComision = ($montoTotal * $porcentaje->cantidad_porcentaje) / 100;
                        
                        \App\Models\Comisione::create([
                            'contrato_id' => $contrato->id,
                            'empleado_id' => $empleado_id,
                            'fecha_comision' => $contrato->fecha_inicio ?? now(),
                            'nombre_paquete' => $contrato->paquete->nombre ?? '',
                            'porcentaje' => $porcentaje->cantidad_porcentaje,
                            'tipo_comision' => $porcentaje->tipo_porcentaje,
                            'monto' => $montoComision,
                            'observaciones' => "Comisión por {$porcentaje->tipo_porcentaje} ({$porcentaje->cantidad_porcentaje}%)",
                            'documento' => 'No',
                            'estado' => 'pendiente'
                        ]);
                    }
                }
            }
        }

        return Redirect::route('contratos.index')
            ->with('success', 'Contrato modificado correctamente.');
    }

    public function destroy($id): RedirectResponse
    {
        Contrato::find($id)->delete();

        return Redirect::route('contratos.index')
            ->with('success', 'Contrato eliminado correctamente.');
    }

    public function cancel($id): RedirectResponse
    {
        $contrato = Contrato::findOrFail($id);
        $contrato->estado = 'cancelado';
        $contrato->save();
        return Redirect::route('contratos.index')
            ->with('success', 'Contrato cancelado correctamente.');
    }

    /**
     * Finalizar un contrato
     */
    public function finalizar($id): RedirectResponse
    {
        $contrato = Contrato::findOrFail($id);
        $contrato->estado = 'finalizado';
        $contrato->save();
        return Redirect::route('contratos.index')
            ->with('success', 'Contrato finalizado correctamente.');
    }

    /**
     * Suspender un contrato
     */
    public function suspender($id): RedirectResponse
    {
        $contrato = Contrato::findOrFail($id);
        $contrato->estado = 'suspendido';
        $contrato->save();
        return Redirect::route('contratos.index')
            ->with('success', 'Contrato suspendido correctamente.');
    }

    /**
     * Reactivar un contrato
     */
    public function reactivar($id): RedirectResponse
    {
        $contrato = Contrato::findOrFail($id);
        $contrato->estado = 'activo';
        $contrato->save();
        return Redirect::route('contratos.index')
            ->with('success', 'Contrato reactivado correctamente.');
    }

    /**
     * Get porcentajes by paquete for AJAX request
     */
    public function getPorcentajesByPaquete($paquete_id)
    {
        $porcentajes = \App\Models\Porcentaje::where('paquete_id', $paquete_id)->get();
        $empleados = \App\Models\Empleado::selectRaw("CONCAT(nombre, ' ', apellido) as nombre_completo, id")
            ->pluck('nombre_completo', 'id');
        
        return response()->json([
            'porcentajes' => $porcentajes,
            'empleados' => $empleados
        ]);
    }

    /**
     * Update observaciones for the specified contract via AJAX
     */
    public function updateObservaciones(Request $request, Contrato $contrato)
    {
        try {
            $request->validate([
                'observaciones' => 'nullable|string|max:1000'
            ]);

            $contrato->update([
                'observaciones' => $request->input('observaciones')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Observaciones actualizadas correctamente'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos: ' . implode(', ', $e->validator->errors()->all())
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateDocumento(Request $request, Contrato $contrato)
    {
        try {
            $request->validate([
                'documento' => 'required|file|mimes:pdf|max:10240' // Máximo 10MB
            ]);

            $hasExistingDocument = $contrato->documento && $contrato->documento !== 'No';

            // Eliminar el documento anterior si existe
            if ($hasExistingDocument && Storage::disk('public')->exists($contrato->documento)) {
                Storage::disk('public')->delete($contrato->documento);
            }

            // Guardar el nuevo documento
            $file = $request->file('documento');
            $fileName = 'contrato_' . $contrato->id . '_' . time() . '.pdf';
            $filePath = $file->storeAs('contratos', $fileName, 'public');

            // Actualizar el registro del contrato
            $contrato->update([
                'documento' => $filePath
            ]);

            $message = $hasExistingDocument 
                ? 'Documento reemplazado correctamente'
                : 'Documento subido correctamente';

            return response()->json([
                'success' => true,
                'message' => $message,
                'file_path' => $filePath
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Archivo inválido: ' . implode(', ', $e->validator->errors()->all())
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear una parcialidad para una comisión padre
     */
    public function crearParcialidad(Request $request)
    {
        try {
            $request->validate([
                'comision_padre_id' => 'required|exists:comisiones,id',
                'monto' => 'required|numeric|min:0.01',
                'observaciones' => 'nullable|string|max:255'
            ]);

            $comisionPadre = Comisione::findOrFail($request->comision_padre_id);
            
            // Verificar que sea una comisión padre (sin comision_padre_id)
            if ($comisionPadre->comision_padre_id !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden crear parcialidades de comisiones padre'
                ], 400);
            }

            // Verificar que el monto no exceda el monto restante de la comisión padre
            $totalParcialidadesExistentes = $comisionPadre->parcialidades()->sum('monto');
            $montoRestante = $comisionPadre->monto - $totalParcialidadesExistentes;
            
            // Usar bccomp para comparación precisa de decimales (tolerancia de 0.01)
            if (bccomp($request->monto, $montoRestante, 2) > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "El monto no puede exceder el monto restante de $" . number_format($montoRestante, 2)
                ], 400);
            }

            // Crear la parcialidad
            $parcialidad = Comisione::create([
                'contrato_id' => $comisionPadre->contrato_id,
                'empleado_id' => $comisionPadre->empleado_id,
                'comision_padre_id' => $comisionPadre->id,
                'fecha_comision' => now(),
                'nombre_paquete' => $comisionPadre->nombre_paquete,
                'porcentaje' => 0, // Las parcialidades no tienen porcentaje
                'tipo_comision' => 'PARCIALIDAD',
                'monto' => $request->monto,
                'observaciones' => $request->observaciones ?? 'Parcialidad de comisión #' . $comisionPadre->id,
                'estado' => 'Pagada'
            ]);

            // Verificar si la comisión padre debe cambiar a "Pagada"
            // Recalcular el total de parcialidades incluyendo la recién creada
            $totalParcialidadesActualizado = $comisionPadre->parcialidades()->sum('monto');
            $montoRestanteActualizado = $comisionPadre->monto - $totalParcialidadesActualizado;
            
            // Si el monto restante es 0 o muy cercano a 0 (tolerancia de 0.01), marcar como pagada
            if (bccomp($montoRestanteActualizado, 0, 2) <= 0) {
                $comisionPadre->update([
                    'estado' => 'Pagada',
                    'fecha_comision' => now() // Actualizar fecha cuando se completa el pago
                ]);
                
                $message = 'Parcialidad creada exitosamente. La comisión padre se ha marcado como pagada al completarse totalmente.';
            } else {
                $message = 'Parcialidad creada exitosamente';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'parcialidad' => $parcialidad->load('empleado'),
                'comision_padre_actualizada' => $comisionPadre->fresh() // Devolver la comisión padre actualizada
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $e->validator->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la parcialidad: ' . $e->getMessage()
            ], 500);
        }
    }
}
