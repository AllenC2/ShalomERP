<?php

namespace App\Http\Controllers;

use App\Models\Comisione;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\ComisioneRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ComisioneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $comisiones = Comisione::paginate();

        return view('comisione.index', compact('comisiones'))
            ->with('i', ($request->input('page', 1) - 1) * $comisiones->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $comisione = new Comisione();
        $contratos = \App\Models\Contrato::pluck('id', 'id')->map(function($id) {
            $contrato = \App\Models\Contrato::find($id);
            return "#{$id} - {$contrato->cliente->nombre} ({$contrato->paquete->nombre})";
        });
        $empleados = \App\Models\Empleado::selectRaw("CONCAT(nombre, ' ', apellido) as nombre_completo, id")
            ->pluck('nombre_completo', 'id');

        return view('comisione.create', compact('comisione', 'contratos', 'empleados'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ComisioneRequest $request): RedirectResponse
    {
        $data = $request->validated();
        
        // Manejar la subida del documento si existe
        if ($request->hasFile('documento')) {
            $file = $request->file('documento');
            
            // Validar que sea un PDF
            if ($file->getClientMimeType() !== 'application/pdf') {
                return back()->withErrors(['documento' => 'El archivo debe ser un PDF válido.'])->withInput();
            }
            
            // Generar nombre único para el archivo
            $fileName = 'comision_' . time() . '_' . uniqid() . '.pdf';
            
            // Guardar el archivo en storage/app/public/comisiones/documentos
            $filePath = $file->storeAs('comisiones/documentos', $fileName, 'public');
            
            // Guardar la ruta en la base de datos
            $data['documento'] = $filePath;
        } else {
            $data['documento'] = 'No';
        }

        Comisione::create($data);

        return Redirect::route('comisiones.index')
            ->with('success', 'Comisión creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $comisione = Comisione::find($id);

        return view('comisione.show', compact('comisione'));
    }
    

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $comisione = Comisione::find($id);
        $contratos = \App\Models\Contrato::pluck('id', 'id')->map(function($id) {
            $contrato = \App\Models\Contrato::find($id);
            return "#{$id} - {$contrato->cliente->nombre} ({$contrato->paquete->nombre})";
        });
        $empleados = \App\Models\Empleado::selectRaw("CONCAT(nombre, ' ', apellido) as nombre_completo, id")
            ->pluck('nombre_completo', 'id');

        return view('comisione.edit', compact('comisione', 'contratos', 'empleados'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ComisioneRequest $request, Comisione $comisione): RedirectResponse
    {
        $data = $request->validated();
        
        // Manejar la subida del documento si existe
        if ($request->hasFile('documento')) {
            $file = $request->file('documento');
            
            // Validar que sea un PDF
            if ($file->getClientMimeType() !== 'application/pdf') {
                return back()->withErrors(['documento' => 'El archivo debe ser un PDF válido.'])->withInput();
            }
            
            // Eliminar archivo anterior si existe y no es 'No'
            if ($comisione->documento && $comisione->documento !== 'No' && \Storage::disk('public')->exists($comisione->documento)) {
                \Storage::disk('public')->delete($comisione->documento);
            }
            
            // Generar nombre único para el archivo
            $fileName = 'comision_' . $comisione->id . '_' . time() . '_' . uniqid() . '.pdf';
            
            // Guardar el archivo en storage/app/public/comisiones/documentos
            $filePath = $file->storeAs('comisiones/documentos', $fileName, 'public');
            
            // Guardar la ruta en los datos
            $data['documento'] = $filePath;
        }
        // Si no se subió un nuevo archivo, mantener el existente
        else {
            unset($data['documento']);
        }

        $comisione->update($data);

        return Redirect::route('comisiones.index')
            ->with('success', 'Comisión modificada correctamente.');
    }

    public function destroy($id): RedirectResponse
    {
        $comisione = Comisione::find($id);
        
        if ($comisione) {
            // Eliminar archivo del storage si existe y no es 'No'
            if ($comisione->documento && $comisione->documento !== 'No' && Storage::disk('public')->exists($comisione->documento)) {
                Storage::disk('public')->delete($comisione->documento);
            }
            
            $comisione->delete();
        }

        return Redirect::route('comisiones.index')
            ->with('success', 'Comisión eliminada correctamente.');
    }

    /**
     * Toggle the estado of the specified resource.
     */
    public function toggleEstado($id)
    {
        try {
            $comision = Comisione::findOrFail($id);
            
            // Alternar entre 'Pendiente' y 'Pagada'
            $nuevoEstado = $comision->estado === 'Pendiente' ? 'Pagada' : 'Pendiente';
            $comision->estado = $nuevoEstado;
            
            // Actualizar fecha_comision según el nuevo estado
            if ($nuevoEstado === 'Pagada') {
                // Si cambia a Pagada, actualizar fecha_comision a la fecha/hora actual
                $comision->fecha_comision = now();
            } else {
                // Si cambia a Pendiente, regresar fecha_comision al created_at original
                $comision->fecha_comision = $comision->created_at;
            }
            
            $comision->save();
            
            return response()->json([
                'success' => true,
                'nuevo_estado' => $comision->estado,
                'badge_class' => $comision->estado === 'Pagada' ? 'bg-success' : 'bg-warning',
                'fecha_comision' => $comision->fecha_comision->locale('es')->isoFormat('D [de] MMMM [de] YYYY')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al actualizar el estado de la comisión'
            ], 500);
        }
    }

    /**
     * Get estados en tiempo real para un contrato específico
     */
    public function getEstadosContrato($contratoId)
    {
        try {
            $comisiones = Comisione::where('contrato_id', $contratoId)
                ->select('id', 'estado', 'fecha_comision', 'updated_at')
                ->get();
            
            $estados = [];
            foreach ($comisiones as $comision) {
                $estados[$comision->id] = [
                    'estado' => $comision->estado,
                    'badge_class' => $comision->estado === 'Pagada' ? 'bg-success' :
                                   ($comision->estado === 'Pendiente' ? 'bg-warning' : 'bg-secondary'),
                    'fecha_comision' => $comision->fecha_comision ?
                                       \Carbon\Carbon::parse($comision->fecha_comision)->locale('es')->isoFormat('D [de] MMMM [de] YYYY') :
                                       null,
                    'updated_at' => $comision->updated_at->timestamp
                ];
            }
            
            return response()->json([
                'success' => true,
                'estados' => $estados,
                'timestamp' => now()->timestamp
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener estados'
            ], 500);
        }
    }

    /**
     * Eliminar documento de la comisión
     */
    public function deleteDocumento($id)
    {
        try {
            \Log::info("Inicio deleteDocumento para comisión ID: $id");
            
            $comisione = Comisione::findOrFail($id);
            \Log::info("Comisión encontrada:", ['documento' => $comisione->documento]);
            
            // Verificar si tiene documento y no es 'No'
            if (!$comisione->documento || $comisione->documento === 'No') {
                \Log::warning("No hay documento para eliminar o ya está marcado como 'No'");
                return response()->json([
                    'success' => false,
                    'message' => 'No hay documento para eliminar'
                ], 400);
            }
            
            // Eliminar archivo del storage
            $documentoPath = $comisione->documento;
            if (Storage::disk('public')->exists($documentoPath)) {
                Storage::disk('public')->delete($documentoPath);
                \Log::info("Archivo eliminado del storage: $documentoPath");
            } else {
                \Log::warning("Archivo no encontrado en storage: $documentoPath");
            }
            
            // Actualizar el registro en la base de datos
            $comisione->documento = 'No';
            $comisione->save();
            \Log::info("Registro actualizado en la base de datos");
            
            return response()->json([
                'success' => true,
                'message' => 'Documento eliminado correctamente'
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Error en deleteDocumento: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el documento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método de prueba para verificar el deleteDocumento
     */
    public function testDeleteDocumento($id)
    {
        return $this->deleteDocumento($id);
    }

    /**
     * Subir nuevo documento para la comisión
     */
    public function uploadDocumento(Request $request, $id)
    {
        try {
            \Log::info("Inicio uploadDocumento para comisión ID: $id");
            
            $comisione = Comisione::findOrFail($id);
            
            // Validar el archivo
            $request->validate([
                'documento' => 'required|file|mimes:pdf,jpg,jpeg,png,gif,bmp,webp|max:10240', // 10MB máximo
            ]);
            
            // Eliminar documento anterior si existe
            if ($comisione->documento && $comisione->documento !== 'No') {
                if (Storage::disk('public')->exists($comisione->documento)) {
                    Storage::disk('public')->delete($comisione->documento);
                    \Log::info("Documento anterior eliminado: " . $comisione->documento);
                }
            }
            
            // Subir nuevo documento
            $file = $request->file('documento');
            $fileName = 'comision_' . $id . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('comisiones/documentos', $fileName, 'public');
            
            // Actualizar el registro
            $comisione->documento = $path;
            $comisione->save();
            
            \Log::info("Nuevo documento subido: $path");
            
            return response()->json([
                'success' => true,
                'message' => 'Documento subido correctamente',
                'documento_url' => Storage::url($path),
                'documento_nombre' => $fileName
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error("Error de validación en uploadDocumento: " . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
            
        } catch (\Exception $e) {
            \Log::error("Error en uploadDocumento: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al subir el documento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una comisión de tipo parcialidad
     */
    public function eliminarParcialidad($id)
    {
        try {
            $comision = Comisione::findOrFail($id);

            // Verificar que sea una parcialidad
            if ($comision->tipo_comision !== 'PARCIALIDAD') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden eliminar comisiones de tipo PARCIALIDAD'
                ], 400);
            }

            // Verificar que tenga comisión padre
            if (!$comision->comision_padre_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta comisión no es una parcialidad válida'
                ], 400);
            }

            // Guardar información para la respuesta
            $comisionPadreId = $comision->comision_padre_id;
            $contratoId = $comision->contrato_id;

            // Eliminar la parcialidad
            $comision->delete();

            return response()->json([
                'success' => true,
                'message' => 'Parcialidad eliminada exitosamente',
                'redirect_url' => route('contratos.comisiones', $contratoId)
            ]);

        } catch (\Exception $e) {
            \Log::error("Error al eliminar parcialidad: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la parcialidad: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar el empleado asignado a una comisión
     */
    public function updateEmpleado(Request $request, $id)
    {
        try {
            $comision = Comisione::findOrFail($id);

            // Validar el empleado
            $request->validate([
                'empleado_id' => 'required|exists:empleados,id'
            ]);

            $empleadoAnterior = $comision->empleado;

            // Actualizar el empleado
            $comision->empleado_id = $request->empleado_id;
            $comision->save();

            // Obtener información del nuevo empleado
            $nuevoEmpleado = $comision->empleado;

            return response()->json([
                'success' => true,
                'message' => 'Empleado actualizado correctamente',
                'empleado' => [
                    'id' => $nuevoEmpleado->id,
                    'nombre' => $nuevoEmpleado->nombre,
                    'apellido' => $nuevoEmpleado->apellido,
                    'nombre_completo' => $nuevoEmpleado->nombre . ' ' . $nuevoEmpleado->apellido,
                    'iniciales' => strtoupper(substr($nuevoEmpleado->nombre ?? 'N', 0, 1)) . strtoupper(substr($nuevoEmpleado->apellido ?? 'A', 0, 1))
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error("Error al actualizar empleado de comisión: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el empleado: ' . $e->getMessage()
            ], 500);
        }
    }
}
