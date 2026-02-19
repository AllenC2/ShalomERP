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

        // Crear el pago
        Pago::create($validatedData);

        // Redirigir al contrato si existe, sino a la lista de pagos
        if (isset($validatedData['contrato_id']) && $validatedData['contrato_id']) {
            return redirect()->route('contratos.show', $validatedData['contrato_id'])
                ->with('success', 'Pago registrado correctamente.');
        }

        return redirect()->route('pagos.index')
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

        return Redirect::route('contratos.show', $pago->contrato_id)
            ->with('success', 'Pago modificado correctamente.');
    }

    public function destroy($id): RedirectResponse
    {
        $pago = Pago::findOrFail($id);
        $contratoId = $pago->contrato_id;

        // Eliminar el pago
        $pago->delete();

        return Redirect::route('pagos.index')
            ->with('success', 'Pago eliminado correctamente.');
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
