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
        $sortBy = $request->input('sort_by', 'id');
        $sortDirection = $request->input('sort_direction', 'desc');

        $pagosQuery = Pago::with(['contrato.cliente', 'creador']);
        if ($searchContrato) {
            $pagosQuery->where('contrato_id', $searchContrato);
        }
        if ($estado && in_array($estado, ['hecho', 'pendiente', 'retrasado'])) {
            $pagosQuery->where('estado', $estado);
        }
        
        $allowedSortColumns = ['id', 'contrato_id', 'monto', 'fecha_pago', 'estado', 'created_by'];
        if (in_array($sortBy, $allowedSortColumns)) {
            $pagosQuery->orderBy($sortBy, $sortDirection === 'asc' ? 'asc' : 'desc');
        } else {
            $pagosQuery->orderBy('id', 'desc');
        }

        $pagos = $pagosQuery->paginate(25);

        return view('pago.index', compact('pagos', 'searchContrato', 'estado', 'sortBy', 'sortDirection'))
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

        if ($contrato_id) {
            $contrato = Contrato::with(['cliente', 'paquete'])->find($contrato_id);
        }

        return view('pago.create', compact('pago', 'contrato_id', 'contrato'));
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

        // Asegurar que el estado sea 'hecho' por defecto si no viene
        $validatedData['estado'] = $validatedData['estado'] ?? 'hecho';
        
        // Asignar el usuario creador
        $validatedData['created_by'] = auth()->id();

        // Crear el pago
        $pago = Pago::create($validatedData);

        // Actualizar contrato si está relacionado
        if ($pago->contrato) {
            $pago->contrato->actualizarProximaFechaPago();
            if ($pago->estado === 'hecho') {
                $pago->contrato->distribuirComisiones($pago);
            }
        }

        return redirect()->route('pagos.show', $pago->id)
            ->with('success', 'Pago registrado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $pago = Pago::with(['contrato.cliente', 'contrato.paquete'])->findOrFail($id);

        // Obtener información de la empresa para mostrar en el recibo
        $infoEmpresa = \App\Models\Ajuste::obtenerInfoEmpresa();

        return view('pago.show', compact('pago', 'infoEmpresa'));
    }

    /**
     * Display a 58mm ticket for the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function ticket($id)
    {
        $pago = Pago::with(['contrato.cliente'])->find($id);
        if (!$pago) {
            abort(404);
        }
        
        $empresa = \App\Models\Ajuste::obtenerInfoEmpresa();
        $cliente = $pago->contrato ? $pago->contrato->cliente : null;
        
        return view('pago.ticket', compact('pago', 'empresa', 'cliente'));
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
        } else {
            unset($validatedData['documento']);
        }

        $pago->update($validatedData);

        // Actualizar contrato
        if ($pago->contrato) {
            $pago->contrato->actualizarProximaFechaPago();
            if ($pago->estado === 'hecho') {
                $pago->contrato->distribuirComisiones($pago);
            }
        }

        return Redirect::route('pagos.show', $pago->id)
            ->with('success', 'Pago modificado correctamente.');
    }

    public function destroy($id): RedirectResponse
    {
        $pago = Pago::findOrFail($id);
        $contratoId = $pago->contrato_id;
        $contrato = $pago->contrato;

        // Eliminar el pago
        $pago->delete();

        if ($contrato) {
            $contrato->actualizarProximaFechaPago();
        }

        return Redirect::route('pagos.revertir_comisiones', ['contrato_id' => $contratoId])
            ->with('warning', 'Pago eliminado. Revisa si es necesario ajustar las comisiones.');
    }

    public function deshacerPago(Request $request, $id)
    {
        try {
            $pago = Pago::findOrFail($id);
            $montoPago = $pago->monto;
            $contrato = $pago->contrato;

            // 1. Eliminar el pago
            $pago->delete();

            // 2. Si el usuario marcó el checkbox de revertir comisiones
            if ($request->boolean('revertir_comisiones')) {
                // Obtenemos todas las parcialidades de comisiones de este contrato
                // Ordenadas de la más reciente y de la de MENOR prioridad (orden DESC) hacia la mayor
                $parcialidades = \App\Models\Comisione::where('contrato_id', $contrato->id)
                    ->where('tipo_comision', 'PARCIALIDAD')
                    ->orderBy('created_at', 'desc')
                    ->orderBy('orden', 'desc') // CRÍTICO: Revertimos desde la menos prioritaria
                    ->get();

                $montoARevertir = $montoPago;

                foreach ($parcialidades as $parcialidad) {
                    if ($montoARevertir <= 0.009) break;

                    $padreId = $parcialidad->comision_padre_id;
                    $padre = $padreId ? \App\Models\Comisione::find($padreId) : null;

                    if ($parcialidad->monto <= $montoARevertir) {
                        // El monto a revertir cubre toda esta parcialidad
                        $montoARevertir -= $parcialidad->monto;
                        $parcialidad->delete();
                        
                        // Actualizamos el padre a Pendiente
                        if ($padre && $padre->estado === 'Pagada') {
                            $padre->update(['estado' => 'Pendiente']);
                        }
                    } else {
                        // El monto a revertir solo reduce una parte de esta parcialidad
                        $nuevoMonto = $parcialidad->monto - $montoARevertir;
                        $parcialidad->update(['monto' => $nuevoMonto]);
                        $montoARevertir = 0;
                        
                        // El padre pasa a Pendiente porque ya no está completamente pagado
                        if ($padre && $padre->estado === 'Pagada') {
                            $padre->update(['estado' => 'Pendiente']);
                        }
                    }
                }
            }

            // 3. Actualizar fechas del contrato
            if ($contrato) {
                $contrato->actualizarProximaFechaPago();
            }

            return response()->json([
                'success' => true, 
                'message' => 'Pago anulado y comisiones actualizadas exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al deshacer el pago: ' . $e->getMessage()
            ], 500);
        }
    }

    public function revertirComisiones($contrato_id)
    {
        $contrato = Contrato::findOrFail($contrato_id);
        
        // Obtener comisiones y sus parcialidades
        $comisiones = $contrato->comisiones()
            ->whereNull('comision_padre_id')
            ->where('tipo_comision', 'NOT LIKE', 'Fija - %')
            ->with(['parcialidades' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->orderBy('orden', 'asc')
            ->get();

        $saldoDisponible = $contrato->saldo_disponible_para_comisiones_tradicionales;
        $totalPagado = $contrato->comisiones()
            ->where('tipo_comision', 'PARCIALIDAD')
            ->where('estado', 'Pagada')
            ->sum('monto');
            
        $diferencia = $totalPagado - $saldoDisponible;

        return view('pago.revertir_comisiones', compact('contrato', 'comisiones', 'saldoDisponible', 'totalPagado', 'diferencia'));
    }

    public function procesarReversionComisiones(Request $request, $contrato_id)
    {
        $request->validate([
            'parcialidad_id' => 'required|exists:comisiones,id',
            'monto' => 'required|numeric|min:0'
        ]);

        $parcialidad = \App\Models\Comisione::where('id', $request->parcialidad_id)
            ->where('contrato_id', $contrato_id)
            ->where('tipo_comision', 'PARCIALIDAD')
            ->firstOrFail();

        $nuevoMonto = $request->monto;

        if ($nuevoMonto == 0) {
            $padreId = $parcialidad->comision_padre_id;
            $parcialidad->delete();
        } else {
            $padreId = $parcialidad->comision_padre_id;
            $parcialidad->update(['monto' => $nuevoMonto]);
        }

        // Actualizar el estado de la comisión padre
        if ($padreId) {
            $comisionPadre = \App\Models\Comisione::find($padreId);
            if ($comisionPadre) {
                $totalPagado = $comisionPadre->parcialidades()->sum('monto');
                if (bccomp($totalPagado, $comisionPadre->monto, 2) < 0) {
                    $comisionPadre->update(['estado' => 'Pendiente']);
                } else {
                    $comisionPadre->update(['estado' => 'Pagada']);
                }
            }
        }

        return redirect()->back()->with('success', 'Parcialidad actualizada correctamente.');
    }

    /**
     * Actualizar el método de pago (AJAX)
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar método de pago'
            ], 500);
        }
    }


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
