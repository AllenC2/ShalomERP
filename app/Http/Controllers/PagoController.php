<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Contrato;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\PagoRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PagoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $searchContrato = $request->input('search_contrato');
        $estado = $request->input('estado');

        $pagosQuery = Pago::query();
        if ($searchContrato) {
            $pagosQuery->where('contrato_id', $searchContrato);
        }
        if ($estado && in_array($estado, ['hecho', 'pendiente', 'retrasado'])) {
            $pagosQuery->where('estado', $estado);
        }
        $pagos = $pagosQuery->paginate(25);

        return view('pago.index', compact('pagos', 'searchContrato', 'estado'))
            ->with('i', ($request->input('page', 1) - 1) * $pagos->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $pago = new Pago();
        $contrato_id = $request->get('contrato_id');
        $contrato = null;
        $montoSugerido = 0;
        $proximoPagoPendiente = null;
        $montoParcialidadSugerido = 0;
        
        if ($contrato_id) {
            $contrato = Contrato::with(['cliente', 'paquete', 'pagos'])->find($contrato_id);
            
            if ($contrato) {
                // Calcular monto sugerido de cuota
                $montoInicial = $contrato->monto_inicial ?? 0;
                $montoBonificacion = $contrato->monto_bonificacion ?? 0;
                $montoFinanciado = $contrato->monto_total - $montoInicial - $montoBonificacion;
                $montoSugerido = $contrato->numero_cuotas > 0 ? $montoFinanciado / $contrato->numero_cuotas : 0;
                
                // Obtener el próximo pago pendiente de tipo "cuota" para parcialidades
                $proximoPagoPendiente = $contrato->pagos()
                    ->where('estado', 'pendiente')
                    ->where('tipo_pago', 'cuota')
                    ->orderBy('fecha_pago', 'asc')
                    ->first();
                
                // Si existe un pago pendiente de tipo cuota, calcular el monto restante después de parcialidades
                if ($proximoPagoPendiente) {
                    // Calcular parcialidades ya aplicadas a esta cuota
                    $parcialidadesAplicadas = $contrato->pagos()
                        ->where('tipo_pago', 'parcialidad')
                        ->where('estado', 'hecho')
                        ->where('pago_padre_id', $proximoPagoPendiente->id)
                        ->sum('monto');
                    
                    // El monto sugerido es el monto restante de la cuota
                    $montoParcialidadSugerido = max(0, $proximoPagoPendiente->monto - $parcialidadesAplicadas);
                    
                    // Agregar información sobre el monto restante al objeto del pago pendiente
                    $proximoPagoPendiente->monto_restante = $montoParcialidadSugerido;
                    $proximoPagoPendiente->parcialidades_aplicadas = $parcialidadesAplicadas;
                }
            }
        }

        return view('pago.create', compact('pago', 'contrato_id', 'contrato', 'montoSugerido', 'proximoPagoPendiente', 'montoParcialidadSugerido'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PagoRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();
        
        // Manejar la subida del documento si existe
        if ($request->hasFile('documento')) {
            $file = $request->file('documento');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('pagos_storage/documentos', $fileName, 'public');
            $validatedData['documento'] = $filePath;
        } else {
            $validatedData['documento'] = null;
        }

        // Procesar la lógica de distribución automática de pagos
        if ($validatedData['contrato_id'] && $validatedData['estado'] === 'hecho') {
            $this->procesarDistribucionAutomaticaPagos($validatedData);
        } else {
            // Lógica original para pagos pendientes o sin contrato
            if ($validatedData['contrato_id']) {
                $contrato = Contrato::find($validatedData['contrato_id']);
                
                if ($contrato) {
                    if ($validatedData['estado'] === 'hecho') {
                        $validatedData['saldo_restante'] = $contrato->calcularSaldoDespuesDePago($validatedData['monto']);
                    } else {
                        $validatedData['saldo_restante'] = $contrato->saldo_pendiente;
                    }
                } else {
                    $validatedData['saldo_restante'] = 0;
                }
            } else {
                $validatedData['saldo_restante'] = 0;
            }

            // Asignar número de cuota automáticamente si es tipo "cuota"
            $this->asignarNumeroCuota($validatedData);

            $pago = Pago::create($validatedData);
        }

        // Redirigir al contrato si existe, sino a la lista de pagos
        if (isset($validatedData['contrato_id']) && $validatedData['contrato_id']) {
            return redirect()->route('contratos.show', $validatedData['contrato_id'])
                ->with('success', 'Pago registrado correctamente.');
        }

        return redirect()->route('pagos.index')
            ->with('success', 'Pago registrado correctamente.');
    }

    /**
     * Procesa la distribución automática de pagos según el monto recibido
     */
    private function procesarDistribucionAutomaticaPagos(array &$validatedData)
    {
        $contrato = Contrato::with(['pagos' => function($query) {
            $query->orderBy('fecha_pago', 'asc');
        }])->find($validatedData['contrato_id']);
        
        if (!$contrato) {
            return;
        }

        // Calcular cuota sugerida
        $montoInicial = $contrato->monto_inicial ?? 0;
        $montoBonificacion = $contrato->monto_bonificacion ?? 0;
        $montoFinanciado = $contrato->monto_total - $montoInicial - $montoBonificacion;
        $cuotaSugerida = $contrato->numero_cuotas > 0 ? $montoFinanciado / $contrato->numero_cuotas : 0;
        
        $montoPago = $validatedData['monto'];
        
        // Obtener pagos pendientes de tipo "cuota" ordenados por fecha
        $pagosPendientes = $contrato->pagos()
            ->where('estado', 'pendiente')
            ->where('tipo_pago', 'cuota')
            ->orderBy('fecha_pago', 'asc')
            ->get();

        if ($cuotaSugerida <= 0) {
            // Si no hay cuota sugerida válida, crear el pago normalmente
            // Forzar tipo_pago a parcialidad para nuevos pagos desde contrato
            $validatedData['tipo_pago'] = 'parcialidad';
            $validatedData['saldo_restante'] = $contrato->calcularSaldoDespuesDePago($validatedData['monto']);
            Pago::create($validatedData);
            return;
        }

        // CASO 1: Pago menor a la cuota sugerida (PARCIALIDAD)
        if ($montoPago < $cuotaSugerida) {
            $this->procesarPagoParcial($validatedData, $contrato, $pagosPendientes, $montoPago, $cuotaSugerida);
        } 
        // CASO 2: Pago igual a la cuota sugerida y hay pagos pendientes
        elseif ($montoPago == $cuotaSugerida && $pagosPendientes->isNotEmpty()) {
            $this->procesarPagoIgualACuota($validatedData, $contrato, $pagosPendientes, $montoPago);
        }
        // CASO 3: Pago mayor a la cuota sugerida o sin pagos pendientes
        else {
            $this->procesarPagoCompleto($validatedData, $contrato, $pagosPendientes, $montoPago, $cuotaSugerida);
        }
    }

    /**
     * Procesa un pago parcial (menor a la cuota sugerida)
     */
    private function procesarPagoParcial(array $validatedData, $contrato, $pagosPendientes, $montoPago, $cuotaSugerida)
    {
        // Buscar el próximo pago pendiente de tipo cuota para asociar la parcialidad
        $proximaCuotaPendiente = $pagosPendientes->where('tipo_pago', 'cuota')->first();
        
        if (!$proximaCuotaPendiente) {
            throw new \Exception('No hay cuotas pendientes para aplicar la parcialidad');
        }

        // Crear el pago como parcialidad asociada al pago padre
        $validatedData['tipo_pago'] = 'parcialidad';
        $validatedData['pago_padre_id'] = $proximaCuotaPendiente->id;
        $validatedData['estado'] = 'hecho';
        $validatedData['saldo_restante'] = $contrato->calcularSaldoDespuesDePago($montoPago);
        $validatedData['observaciones'] = ($validatedData['observaciones'] ?? '') . " Parcialidad aplicada a cuota #{$proximaCuotaPendiente->id}.";
        
        $parcialidadCreada = Pago::create($validatedData);

        // Refrescar la relación para asegurar que incluya la nueva parcialidad
        $proximaCuotaPendiente->load('parcialidades');
        
        // Verificar si las parcialidades aplicadas (incluyendo la nueva) cubren completamente la cuota
        $totalParcialidades = $proximaCuotaPendiente->parcialidades()
            ->where('estado', 'hecho')
            ->sum('monto');
        
        // Obtener el monto original de la cuota (que es el monto actual ya que no lo modificamos)
        $montoOriginalCuota = $proximaCuotaPendiente->monto;
        
        // Debug: agregar información a las observaciones para tracking
        \Log::info("Verificando completado de cuota #{$proximaCuotaPendiente->id}: Total parcialidades: {$totalParcialidades}, Monto original: {$montoOriginalCuota}");
            
        if ($totalParcialidades >= $montoOriginalCuota) {
            // Las parcialidades cubren completamente la cuota
            $observacionOriginal = $proximaCuotaPendiente->observaciones ?? '';
            
            // Extraer el número de cuota de las observaciones originales si existe
            $numeroCuota = '';
            if (preg_match('/Cuota (\d+) de (\d+)/', $observacionOriginal, $matches)) {
                $numeroCuota = "Cuota {$matches[1]} de {$matches[2]} - ";
            } else {
                // Si no existe en las observaciones, calcularlo basado en la posición
                $cuotasAnteriores = Pago::where('contrato_id', $contrato->id)
                    ->where('tipo_pago', 'cuota')
                    ->where('estado', 'hecho')
                    ->where('id', '<', $proximaCuotaPendiente->id)
                    ->where('monto', '>', 0)
                    ->count();
                $numeroActual = $cuotasAnteriores + 1;
                $numeroCuota = "Cuota {$numeroActual} de {$contrato->numero_cuotas} - ";
            }
            
            $nuevaObservacion = $numeroCuota . "Liquidado completamente por parcialidades.";
            
            // Conservar la fecha original de la cuota (no cambiarla cuando se completa por parcialidades)
            $proximaCuotaPendiente->update([
                'estado' => 'hecho',
                'observaciones' => $nuevaObservacion
            ]);
            
            // Actualizar las observaciones de la parcialidad para indicar que liquidó la cuota
            $parcialidadCreada->update([
                'observaciones' => trim($validatedData['observaciones'] . " Esta parcialidad completó el pago de la cuota #{$proximaCuotaPendiente->id}.")
            ]);
        } else {
            // Las parcialidades aún no cubren la cuota completa - actualizar solo observaciones
            $montoPendiente = $montoOriginalCuota - $totalParcialidades;
            
            $observacionOriginal = $proximaCuotaPendiente->observaciones ?? '';
            
            // Extraer el número de cuota de las observaciones originales si existe
            $numeroCuota = '';
            if (preg_match('/Cuota (\d+) de (\d+)/', $observacionOriginal, $matches)) {
                $numeroCuota = "Cuota {$matches[1]} de {$matches[2]} - ";
            } else {
                // Si no existe en las observaciones, calcularlo basado en la posición
                $cuotasAnteriores = Pago::where('contrato_id', $contrato->id)
                    ->where('tipo_pago', 'cuota')
                    ->where('estado', 'hecho')
                    ->where('id', '<', $proximaCuotaPendiente->id)
                    ->where('monto', '>', 0)
                    ->count();
                $numeroActual = $cuotasAnteriores + 1;
                $numeroCuota = "Cuota {$numeroActual} de {$contrato->numero_cuotas} - ";
            }
            
            $nuevaObservacion = $numeroCuota . "Parcialidades aplicadas: $" . number_format($totalParcialidades, 2) . " de $" . number_format($montoOriginalCuota, 2) . " totales. Pendiente: $" . number_format($montoPendiente, 2) . ".";
            
            $proximaCuotaPendiente->update([
                'observaciones' => $nuevaObservacion
            ]);
        }
    }

    /**
     * Procesa un pago que es exactamente igual a la cuota sugerida
     * En lugar de crear una parcialidad, actualiza el estado del próximo pago pendiente
     */
    private function procesarPagoIgualACuota(array $validatedData, $contrato, $pagosPendientes, $montoPago)
    {
        $proximoPagoPendiente = $pagosPendientes->first();
        
        // Actualizar el pago pendiente con los datos del nuevo pago
        $observacionOriginal = $proximoPagoPendiente->observaciones ?? '';
        $nuevaObservacion = trim($observacionOriginal . " Pagado mediante " . strtolower($validatedData['metodo_pago']) . " el " . date('d/m/Y H:i', strtotime($validatedData['fecha_pago'])) . ".");
        
        $proximoPagoPendiente->update([
            'metodo_pago' => $validatedData['metodo_pago'],
            'estado' => 'hecho',
            'documento' => $validatedData['documento'] ?? null,
            'observaciones' => $nuevaObservacion,
            'saldo_restante' => $contrato->calcularSaldoDespuesDePago($montoPago)
        ]);
        
        // No crear un nuevo pago, solo hemos actualizado el existente
    }

    /**
     * Procesa un pago completo (igual o mayor a la cuota sugerida)
     */
    private function procesarPagoCompleto(array $validatedData, $contrato, $pagosPendientes, $montoPago, $cuotaSugerida)
    {
        $montoRestante = $montoPago;
        $pagosModificados = [];

        // Cubrir pagos pendientes completos
        foreach ($pagosPendientes as $pagoPendiente) {
            if ($montoRestante >= $pagoPendiente->monto) {
                // Cubrir este pago completamente
                $montoRestante -= $pagoPendiente->monto;
                
                // Actualizar el pago pendiente con los datos del nuevo pago
                $observacionOriginal = $pagoPendiente->observaciones ?? '';
                $nuevaObservacion = trim($observacionOriginal . " Pagado mediante " . strtolower($validatedData['metodo_pago']) . " el " . date('d/m/Y H:i', strtotime($validatedData['fecha_pago'])) . ".");
                
                $pagoPendiente->update([
                    'metodo_pago' => $validatedData['metodo_pago'],
                    'estado' => 'hecho',
                    'documento' => $validatedData['documento'] ?? null,
                    'observaciones' => $nuevaObservacion,
                    'saldo_restante' => $contrato->calcularSaldoDespuesDePago($montoPago)
                ]);
                
                $pagosModificados[] = $pagoPendiente->id;
            } else {
                // Este pago no se puede cubrir completamente
                break;
            }
        }

        // Solo crear un nuevo pago si hay monto restante que no se aplicó a ningún pago pendiente
        $pagoCreado = null;
        if ($montoRestante > 0) {
            // Crear el pago original como cuota regular por el monto restante
            $validatedData['tipo_pago'] = 'cuota';
            $validatedData['estado'] = 'hecho';
            $validatedData['monto'] = $montoRestante; // Solo el monto que no se aplicó
            $validatedData['saldo_restante'] = $contrato->calcularSaldoDespuesDePago($montoPago);
            
            if (!empty($pagosModificados)) {
                $validatedData['observaciones'] = trim(($validatedData['observaciones'] ?? '') . " Cubrió automáticamente " . count($pagosModificados) . " pago(s) pendiente(s). Monto restante de pago: $" . number_format($montoRestante, 2) . ".");
            }
            
            // Asignar número de cuota automáticamente
            $this->asignarNumeroCuota($validatedData);
            
            $pagoCreado = Pago::create($validatedData);

            // Aplicar el monto restante al siguiente pago pendiente si existe
            $siguientePagoPendiente = $pagosPendientes->whereNotIn('id', $pagosModificados)->first();
            
            if ($siguientePagoPendiente) {
                // Crear pago de parcialidad por el excedente
                $pagoParcialidad = Pago::create([
                    'contrato_id' => $contrato->id,
                    'tipo_pago' => 'parcialidad',
                    'monto' => $montoRestante,
                    'fecha_pago' => $validatedData['fecha_pago'],
                    'metodo_pago' => $validatedData['metodo_pago'],
                    'estado' => 'hecho',
                    'observaciones' => "Excedente de pago principal (Pago #{$pagoCreado->id}) aplicado como parcialidad.",
                    'saldo_restante' => $contrato->calcularSaldoDespuesDePago($montoPago) // Ya calculado arriba
                ]);

                // Reducir el monto del siguiente pago pendiente
                $nuevoMonto = max(0, $siguientePagoPendiente->monto - $montoRestante);
                $observacionOriginal = $siguientePagoPendiente->observaciones ?? '';
                
                // Extraer o generar el número de cuota
                $numeroCuota = '';
                if (preg_match('/Cuota (\d+) de (\d+)/', $observacionOriginal, $matches)) {
                    $numeroCuota = "Cuota {$matches[1]} de {$matches[2]} - ";
                } else {
                    // Si no existe en las observaciones, calcularlo basado en la posición
                    $cuotasAnteriores = Pago::where('contrato_id', $contrato->id)
                        ->where('tipo_pago', 'cuota')
                        ->where('estado', 'hecho')
                        ->where('id', '<', $siguientePagoPendiente->id)
                        ->where('monto', '>', 0)
                        ->count();
                    $numeroActual = $cuotasAnteriores + 1;
                    $numeroCuota = "Cuota {$numeroActual} de {$contrato->numero_cuotas} - ";
                }
                
                $nuevaObservacion = $numeroCuota . "Reducido por excedente de $" . number_format($montoRestante, 2) . " (Parcialidad #{$pagoParcialidad->id}).";
                
                $siguientePagoPendiente->update([
                    'monto' => $nuevoMonto,
                    'observaciones' => $nuevaObservacion
                ]);
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $pago = Pago::with([
            'contrato.cliente', 
            'contrato.paquete', 
            'contrato.pagos',
            'parcialidades' => function($query) {
                $query->orderBy('created_at', 'asc');
            },
            'pagoPadre'
        ])->findOrFail($id);

        // Obtener información de la empresa para mostrar en el recibo
        $infoEmpresa = \App\Models\Ajuste::obtenerInfoEmpresa();

        return view('pago.show', compact('pago', 'infoEmpresa'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $pago = Pago::findOrFail($id);

        return view('pago.edit', compact('pago'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PagoRequest $request, Pago $pago): RedirectResponse
    {
        $validatedData = $request->validated();
        
        // Manejar la subida del documento si existe
        if ($request->hasFile('documento')) {
            // Eliminar el documento anterior si existe
            if ($pago->documento && \Storage::disk('public')->exists($pago->documento)) {
                \Storage::disk('public')->delete($pago->documento);
            }
            
            $file = $request->file('documento');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('pagos_storage/documentos', $fileName, 'public');
            $validatedData['documento'] = $filePath;
        }
        // Si no hay archivo nuevo, mantener el existente
        else {
            unset($validatedData['documento']);
        }

        // Si se está cambiando de pendiente a hecho y hay contrato, aplicar distribución automática
        if ($pago->estado === 'pendiente' && $validatedData['estado'] === 'hecho' && $pago->contrato_id) {
            // Eliminar el pago actual temporalmente para recalcular
            $pagoData = $pago->toArray();
            $pagoId = $pago->id;
            $contratoId = $pago->contrato_id;
            
            $pago->delete();
            
            // Aplicar la nueva data al array del pago
            foreach ($validatedData as $key => $value) {
                if ($key !== 'documento' || isset($validatedData['documento'])) {
                    $pagoData[$key] = $value;
                }
            }
            
            // Procesar con distribución automática
            $this->procesarDistribucionAutomaticaPagos($pagoData);
            
            return Redirect::route('contratos.show', $contratoId)
                ->with('success', 'Pago modificado y redistribuido correctamente.');
        } else {
            // Lógica original para otros casos
            if ($validatedData['contrato_id']) {
                $contrato = Contrato::find($validatedData['contrato_id']);
                
                if ($contrato) {
                    if ($validatedData['estado'] === 'hecho') {
                        $validatedData['saldo_restante'] = $contrato->calcularSaldoDespuesDePago($validatedData['monto'], $pago->id);
                    } else {
                        $validatedData['saldo_restante'] = $contrato->saldo_pendiente;
                    }
                } else {
                    $validatedData['saldo_restante'] = 0;
                }
            } else {
                $validatedData['saldo_restante'] = 0;
            }

            $pago->update($validatedData);

            return Redirect::route('contratos.show', $pago->contrato_id)
                ->with('success', 'Pago modificado correctamente.');
        }
    }

    public function destroy($id): RedirectResponse
    {
        $pago = Pago::findOrFail($id);
        $contratoId = $pago->contrato_id;
        
        // Eliminar el pago
        $pago->delete();
        
        // Si había un contrato asociado, recalcular los saldos de los pagos restantes
        if ($contratoId) {
            $contrato = Contrato::find($contratoId);
            if ($contrato) {
                $contrato->recalcularSaldosPagos();
            }
        }

        return Redirect::route('pagos.index')
            ->with('success', 'Pago eliminado correctamente.');
    }

    /**
     * Verifica si una parcialidad liquidará completamente el próximo pago pendiente
     */
    public function verificarLiquidacionParcialidad(Request $request)
    {
        $contratoId = $request->input('contrato_id');
        $montoParcialidad = (float) $request->input('monto');

        if (!$contratoId || !$montoParcialidad) {
            return response()->json(['error' => 'Datos insuficientes'], 400);
        }

        $contrato = Contrato::find($contratoId);
        if (!$contrato) {
            return response()->json(['error' => 'Contrato no encontrado'], 404);
        }

        // Calcular cuota sugerida para verificar si es parcialidad
        $montoInicial = $contrato->monto_inicial ?? 0;
        $montoBonificacion = $contrato->monto_bonificacion ?? 0;
        $montoFinanciado = $contrato->monto_total - $montoInicial - $montoBonificacion;
        $cuotaSugerida = $contrato->numero_cuotas > 0 ? $montoFinanciado / $contrato->numero_cuotas : 0;

        // Obtener el próximo pago pendiente de tipo "cuota"
        $proximoPagoPendiente = $contrato->pagos()
            ->where('estado', 'pendiente')
            ->where('tipo_pago', 'cuota')
            ->orderBy('fecha_pago', 'asc')
            ->first();

        // Caso especial: pago igual a la cuota sugerida
        if ($montoParcialidad == $cuotaSugerida && $proximoPagoPendiente) {
            return response()->json([
                'es_parcialidad' => false,
                'es_cuota_exacta' => true,
                'pago_pendiente' => [
                    'id' => $proximoPagoPendiente->id,
                    'monto' => $proximoPagoPendiente->monto,
                    'fecha_pago' => $proximoPagoPendiente->fecha_pago->format('d/m/Y H:i')
                ],
                'mensaje' => '¡Perfecto! Este monto actualizará directamente el pago pendiente del ' . $proximoPagoPendiente->fecha_pago->format('d/m/Y H:i') . ' como pagado.'
            ]);
        }

        // Solo verificar si efectivamente es una parcialidad (menor a cuota sugerida)
        if ($montoParcialidad >= $cuotaSugerida) {
            return response()->json([
                'es_parcialidad' => false,
                'mensaje' => 'El monto ingresado es mayor a la cuota sugerida ($' . number_format($cuotaSugerida, 2) . ')'
            ]);
        }

        if (!$proximoPagoPendiente) {
            return response()->json([
                'es_parcialidad' => true,
                'hay_pendientes' => false,
                'mensaje' => 'No hay pagos de cuota pendientes para aplicar la parcialidad.'
            ]);
        }

        // Calcular el total de parcialidades ya aplicadas a esta cuota
        $totalParcialidadesExistentes = $proximoPagoPendiente->parcialidades()
            ->where('estado', 'hecho')
            ->sum('monto');

        // El monto restante se calcula basándose en la cuota original, no en el monto actual de la cuota
        $montoRestanteDespuesDeParcialidad = $cuotaSugerida - $totalParcialidadesExistentes - $montoParcialidad;

        if ($montoRestanteDespuesDeParcialidad <= 0) {
            return response()->json([
                'es_parcialidad' => true,
                'hay_pendientes' => true,
                'liquida_completamente' => true,
                'pago_pendiente' => [
                    'id' => $proximoPagoPendiente->id,
                    'monto' => $proximoPagoPendiente->monto,
                    'fecha_pago' => $proximoPagoPendiente->fecha_pago->format('d/m/Y H:i')
                ],
                'mensaje' => '¡Esta parcialidad liquidará completamente el pago pendiente del ' . $proximoPagoPendiente->fecha_pago->format('d/m/Y H:i') . ' por $' . number_format($cuotaSugerida, 2) . '!'
            ]);
        } else {
            return response()->json([
                'es_parcialidad' => true,
                'hay_pendientes' => true,
                'liquida_completamente' => false,
                'pago_pendiente' => [
                    'id' => $proximoPagoPendiente->id,
                    'monto' => $proximoPagoPendiente->monto,
                    'fecha_pago' => $proximoPagoPendiente->fecha_pago->format('d/m/Y H:i')
                ],
                'monto_restante' => $montoRestanteDespuesDeParcialidad,
                'mensaje' => 'Esta parcialidad reducirá el pago pendiente del ' . $proximoPagoPendiente->fecha_pago->format('d/m/Y H:i') . ' de $' . number_format($cuotaSugerida, 2) . ' a $' . number_format($montoRestanteDespuesDeParcialidad, 2) . '.'
            ]);
        }
    }

    /**
     * Actualizar el método de pago
     */
    public function updateMetodoPago(Request $request, $id)
    {
        try {
            $request->validate([
                'metodo_pago' => 'required|in:' . implode(',', array_keys(Pago::METODOS_PAGO))
            ]);

            $pago = Pago::findOrFail($id);
            $metodoPagoAnterior = $pago->metodo_pago;
            $pago->metodo_pago = $request->metodo_pago;
            $pago->save();

            return response()->json([
                'success' => true,
                'message' => 'Método de pago actualizado correctamente de "' . (Pago::METODOS_PAGO[$metodoPagoAnterior] ?? $metodoPagoAnterior) . '" a "' . (Pago::METODOS_PAGO[$pago->metodo_pago] ?? $pago->metodo_pago) . '"',
                'metodo_pago' => $pago->metodo_pago,
                'metodo_pago_label' => Pago::METODOS_PAGO[$pago->metodo_pago] ?? $pago->metodo_pago
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Método de pago no válido',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pago no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Deshacer un pago según su tipo
     */
    public function deshacerPago(Request $request, $id)
    {
        try {
            $pago = Pago::with('contrato')->findOrFail($id);
            
            // Validar que el pago esté en estado "hecho"
            if (strtolower($pago->estado) !== 'hecho') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden deshacer pagos con estado "Hecho"'
                ], 422);
            }

            $contrato = $pago->contrato;
            if (!$contrato) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contrato asociado no encontrado'
                ], 404);
            }

            // Procesar según el tipo de pago
            $tipoPago = strtolower($pago->tipo_pago);
            
            switch ($tipoPago) {
                case 'inicial':
                case 'bonificacion':
                case 'bonificación':
                    $this->deshacerPagoInicialOBonificacion($pago, $contrato);
                    $mensaje = "Pago de {$pago->tipo_pago} deshecho exitosamente. Se agregó el monto al saldo restante.";
                    break;
                    
                case 'cuota':
                    $this->deshacerPagoCuota($pago, $contrato);
                    $mensaje = "Pago de cuota deshecho exitosamente. La cuota volvió a estado pendiente.";
                    break;
                    
                case 'parcialidad':
                    $this->deshacerPagoParcialidad($pago, $contrato);
                    $mensaje = "Pago de parcialidad eliminado exitosamente.";
                    break;
                    
                default:
                    return response()->json([
                        'success' => false,
                        'message' => "Tipo de pago '{$pago->tipo_pago}' no soportado para deshacer"
                    ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'pago_id' => $id
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pago no encontrado'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error al deshacer pago: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno al deshacer el pago: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deshacer pago inicial o bonificación
     */
    private function deshacerPagoInicialOBonificacion(Pago $pago, Contrato $contrato)
    {
        $montoOriginal = $pago->monto;
        
        // Agregar el monto al saldo restante
        $nuevoSaldoRestante = $pago->saldo_restante + $montoOriginal;
        
        // Actualizar el pago
        $pago->update([
            'saldo_restante' => $nuevoSaldoRestante,
            'monto' => 0,
            'observaciones' => ($pago->observaciones ? $pago->observaciones . ' | ' : '') . 
                              "Pago deshecho el " . now()->format('d/m/Y H:i:s') . 
                              " (monto original: $" . number_format($montoOriginal, 2) . ")"
        ]);
        
        // El estado se mantiene como "hecho" pero al tener monto 0 no cuenta en las sumas
    }

    /**
     * Deshacer pago de cuota
     */
    private function deshacerPagoCuota(Pago $pago, Contrato $contrato)
    {
        // Verificar si la cuota tiene parcialidades asociadas
        $parcialidades = $pago->parcialidades()->where('estado', 'hecho')->get();
        $montoTotalParcialidades = $parcialidades->sum('monto');
        
        if ($parcialidades->isNotEmpty()) {
            // Si tiene parcialidades, primero las eliminamos
            foreach ($parcialidades as $parcialidad) {
                $parcialidad->delete();
            }
        }

        // Calcular el monto original de la cuota del contrato
        $montoCuotaOriginal = $pago->monto_original_cuota;
        
        // Calcular el nuevo saldo restante considerando tanto el monto de la cuota como las parcialidades
        $montoTotalARestaurar = $pago->monto + $montoTotalParcialidades;
        $nuevoSaldoRestante = $pago->saldo_restante + $montoTotalARestaurar;
        
        // Calcular el número de cuota basado en cuántas cuotas "hechas" hay antes de esta
        // Excluimos pagos con monto = 0 (que son pagos deshebhos)
        $cuotasAnteriores = Pago::where('contrato_id', $contrato->id)
            ->where('tipo_pago', 'cuota')
            ->where('estado', 'hecho')
            ->where('id', '<', $pago->id)
            ->where('monto', '>', 0)
            ->count();
        
        $numeroCuota = $cuotasAnteriores + 1;
        
        // Reiniciar las observaciones con el formato original
        $observacionesOriginales = "Cuota {$numeroCuota} de {$contrato->numero_cuotas}";
        
        // Actualizar el pago restaurando el monto original de la cuota
        $pago->update([
            'monto' => $montoCuotaOriginal,
            'numero_cuota' => $numeroCuota, // Reasignar el número de cuota
            'saldo_restante' => $nuevoSaldoRestante,
            'estado' => 'pendiente',
            'observaciones' => $observacionesOriginales
        ]);
    }

    /**
     * Deshacer pago de parcialidad (eliminarlo y restaurar el monto al pago padre)
     */
    private function deshacerPagoParcialidad(Pago $pago, Contrato $contrato)
    {
        // Verificar que la parcialidad tenga un pago padre
        $pagoPadre = $pago->pagoPadre;
        if (!$pagoPadre) {
            throw new \Exception('La parcialidad no tiene un pago padre asociado');
        }

        // Verificar que el pago padre sea una cuota
        if ($pagoPadre->tipo_pago !== 'cuota') {
            throw new \Exception('El pago padre debe ser una cuota');
        }

        $montoParcialidad = $pago->monto;
        
        // Calcular el nuevo saldo restante
        $nuevoSaldoRestante = $pago->saldo_restante + $montoParcialidad;
        
        // Actualizar el saldo_restante del pago anterior
        $pagoAnterior = Pago::where('contrato_id', $contrato->id)
            ->where('id', '<', $pago->id)
            ->orderBy('id', 'desc')
            ->first();
            
        if ($pagoAnterior) {
            $pagoAnterior->update(['saldo_restante' => $nuevoSaldoRestante]);
        }
        
        // Actualizar saldo_restante de pagos posteriores si los hay
        $pagosPosteriores = Pago::where('contrato_id', $contrato->id)
            ->where('id', '>', $pago->id)
            ->orderBy('id', 'asc')
            ->get();
            
        foreach ($pagosPosteriores as $pagoPosterior) {
            $pagoPosterior->update(['saldo_restante' => $nuevoSaldoRestante]);
        }
        
        // Si el pago padre está completamente pagado por parcialidades, revertir su estado
        $totalParcialidadesRestantes = $pagoPadre->parcialidades()
            ->where('id', '!=', $pago->id) // Excluir la parcialidad que estamos eliminando
            ->where('estado', 'hecho')
            ->sum('monto');
        
        // Calcular el monto original de la cuota
        $montoCuotaOriginal = $pagoPadre->monto_original_cuota;
        
        // Preservar la fecha original de la cuota antes de cualquier actualización
        // Esto garantiza que al deshacer parcialidades, la cuota mantenga su fecha original
        $fechaOriginalCuota = $pagoPadre->fecha_pago;
        
        if ($totalParcialidadesRestantes >= $montoCuotaOriginal) {
            // Aún hay suficientes parcialidades para cubrir la cuota completa
            // Mantener el monto original, solo cambiar el estado
            $pagoPadre->update([
                'estado' => 'hecho',
                'monto' => $montoCuotaOriginal,  // Mantener monto original
                'fecha_pago' => $fechaOriginalCuota  // Conservar fecha original
            ]);
        } elseif ($totalParcialidadesRestantes > 0) {
            // Hay algunas parcialidades pero no cubren la cuota completa
            $montoPendiente = $montoCuotaOriginal - $totalParcialidadesRestantes;
            
            // Extraer el número de cuota de las observaciones si existe
            $observacionesOriginales = $pagoPadre->observaciones ?? '';
            $numeroCuota = '';
            if (preg_match('/Cuota (\d+) de (\d+)/', $observacionesOriginales, $matches)) {
                $numeroCuota = "Cuota {$matches[1]} de {$matches[2]} - ";
            } else {
                // Calcular basado en la posición
                $cuotasAnteriores = Pago::where('contrato_id', $contrato->id)
                    ->where('tipo_pago', 'cuota')
                    ->where('estado', 'hecho')
                    ->where('id', '<', $pagoPadre->id)
                    ->where('monto', '>', 0)
                    ->count();
                $numeroActual = $cuotasAnteriores + 1;
                $numeroCuota = "Cuota {$numeroActual} de {$contrato->numero_cuotas} - ";
            }
            
            $nuevaObservacion = $numeroCuota . "Parcialidades aplicadas: $" . number_format($totalParcialidadesRestantes, 2) . " de $" . number_format($montoCuotaOriginal, 2) . " totales. Pendiente: $" . number_format($montoPendiente, 2) . ".";
            
            $pagoPadre->update([
                'estado' => 'pendiente',
                'monto' => $montoCuotaOriginal,  // Mantener monto original, no el reducido
                'observaciones' => $nuevaObservacion,
                'fecha_pago' => $fechaOriginalCuota  // Conservar fecha original
            ]);
        } else {
            // No quedan parcialidades, restaurar la cuota a su estado original
            $cuotasAnteriores = Pago::where('contrato_id', $contrato->id)
                ->where('tipo_pago', 'cuota')
                ->where('estado', 'hecho')
                ->where('id', '<', $pagoPadre->id)
                ->where('monto', '>', 0)
                ->count();
            
            $numeroCuota = $cuotasAnteriores + 1;
            $observacionesOriginales = "Cuota {$numeroCuota} de {$contrato->numero_cuotas}";
            
            $pagoPadre->update([
                'estado' => 'pendiente',
                'monto' => $montoCuotaOriginal,
                'observaciones' => $observacionesOriginales,
                'fecha_pago' => $fechaOriginalCuota  // Conservar fecha original
            ]);
        }
        
        // Eliminar el pago de parcialidad
        $pago->delete();
    }

    /**
     * Calcula el número de cuota automáticamente basado en la fecha de pago
     */
    private function calcularNumeroCuota($contratoId, $fechaPago)
    {
        // Obtener todos los pagos de tipo "cuota" del contrato ordenados por fecha
        $cuotasExistentes = Pago::where('contrato_id', $contratoId)
            ->where('tipo_pago', 'cuota')
            ->orderBy('fecha_pago', 'asc')
            ->get();

        // Si no hay cuotas existentes, esta será la cuota #1
        if ($cuotasExistentes->isEmpty()) {
            return 1;
        }

        // Contar cuántas cuotas tienen fecha anterior o igual a la fecha del nuevo pago
        $fechaPagoCarbon = \Carbon\Carbon::parse($fechaPago);
        $cuotasAnteriores = $cuotasExistentes->filter(function ($cuota) use ($fechaPagoCarbon) {
            return \Carbon\Carbon::parse($cuota->fecha_pago)->lte($fechaPagoCarbon);
        })->count();

        // El número de cuota será el siguiente
        return $cuotasAnteriores + 1;
    }

    /**
     * Asigna automáticamente el número de cuota si el tipo de pago es "cuota"
     */
    private function asignarNumeroCuota(&$validatedData)
    {
        if (isset($validatedData['tipo_pago']) && 
            $validatedData['tipo_pago'] === 'cuota' && 
            isset($validatedData['contrato_id']) && 
            isset($validatedData['fecha_pago'])) {
            
            $validatedData['numero_cuota'] = $this->calcularNumeroCuota(
                $validatedData['contrato_id'], 
                $validatedData['fecha_pago']
            );
        }
    }

    /**
     * Subir documento adjunto a un pago
     */
    public function uploadDocument(Request $request, $id)
    {
        $request->validate([
            'documento' => 'required|file|mimes:pdf,jpg,jpeg,png,gif,bmp,webp,doc,docx,xls,xlsx|max:10240' // 10MB max
        ]);

        $pago = Pago::findOrFail($id);

        if ($request->hasFile('documento')) {
            // Eliminar documento anterior si existe (soporta rutas anteriores en public/ y nuevas en storage)
            if ($pago->documento) {
                // Intentar borrar desde el disco public (storage)
                if (Storage::disk('public')->exists($pago->documento)) {
                    Storage::disk('public')->delete($pago->documento);
                } else {
                    // Fallback a archivo fisico en public/
                    $oldPath = public_path($pago->documento);
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }
            }

            $file = $request->file('documento');
            $extension = $file->getClientOriginalExtension();
            $filename = 'pago_' . $pago->id . '_' . time() . '.' . $extension;

            // Guardar en el disco public (storage/app/public/pagos_storage/documentos)
            $storedPath = $file->storeAs('pagos_storage/documentos', $filename, 'public');

            // Actualizar el registro con la ruta relativa en storage (sin prefijo /storage)
            $pago->documento = $storedPath; // p.ej. pagos_storage/documentos/archivo.pdf
            $pago->save();

            return response()->json([
                'success' => true,
                'message' => 'Documento subido exitosamente',
                'documento_url' => Storage::url($pago->documento), // /storage/pagos_storage/documentos/...
                'documento_nombre' => basename($storedPath)
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se recibió ningún archivo'
        ], 400);
    }

    /**
     * Eliminar documento adjunto de un pago
     */
    public function deleteDocumento($id)
    {
        $pago = Pago::findOrFail($id);

        if ($pago->documento) {
            // Eliminar archivo del disco public si existe; si no, intentar en public/
            if (Storage::disk('public')->exists($pago->documento)) {
                Storage::disk('public')->delete($pago->documento);
            } else {
                $filePath = public_path($pago->documento);
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }

            // Limpiar campo en la base de datos
            $pago->documento = null;
            $pago->save();

            return response()->json([
                'success' => true,
                'message' => 'Documento eliminado exitosamente'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No hay documento para eliminar'
        ], 400);
    }

}
