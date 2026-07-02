<?php

namespace App\Http\Controllers;

use App\Models\Paquete;
use App\Models\Porcentaje;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\PaqueteRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaqueteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $paquetes = Paquete::with('porcentajes')->withCount('contratos')->paginate();

        // Obtener todos los tipos de porcentaje únicos
        $tiposPorcentajeDB = Porcentaje::select('tipo_porcentaje')
            ->distinct()
            ->pluck('tipo_porcentaje')
            ->toArray();

        // Obtener el orden guardado previamente
        $prioridadesGuardadas = \App\Models\Ajuste::obtener('prioridades_comisiones_globales', []);

        // Ordenar según el orden guardado
        $tiposPorcentajeUnicos = collect($prioridadesGuardadas)->filter(function($tipo) use ($tiposPorcentajeDB) {
            return in_array($tipo, $tiposPorcentajeDB);
        });

        // Agregar los tipos nuevos que no estén en el orden guardado
        $nuevosTipos = array_diff($tiposPorcentajeDB, $prioridadesGuardadas);
        foreach ($nuevosTipos as $nuevoTipo) {
            $tiposPorcentajeUnicos->push($nuevoTipo);
        }

        return view('paquete.index', compact('paquetes', 'tiposPorcentajeUnicos'))
            ->with('i', ($request->input('page', 1) - 1) * $paquetes->perPage());
    }

    /**
     * Actualizar las prioridades globalmente para todos los paquetes
     */
    public function actualizarPrioridadesGlobales(Request $request): RedirectResponse
    {
        $request->validate([
            'tipos_porcentaje' => 'required|array'
        ]);

        $tipos = $request->tipos_porcentaje; // Array de strings en el nuevo orden

        DB::beginTransaction();
        try {
            foreach ($tipos as $index => $tipo) {
                // El índice es 0-based, así que la prioridad es $index + 1
                $prioridad = $index + 1;
                Porcentaje::where('tipo_porcentaje', $tipo)->update(['orden' => $prioridad]);
            }

            // Guardar esta configuración para futuros usos
            \App\Models\Ajuste::establecer('prioridades_comisiones_globales', $tipos, 'json', 'Orden global de prioridades para comisiones');

            DB::commit();

            return redirect()->route('paquetes.index')
                ->with('success', 'Prioridades globales actualizadas correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('paquetes.index')
                ->with('error', 'Ocurrió un error al actualizar las prioridades: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $paquete = new Paquete();

        return view('paquete.create', compact('paquete'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PaqueteRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Crear el paquete
            $paquete = Paquete::create($request->validated());

            // Crear los porcentajes asociados solo si existen
            if ($request->has('porcentajes') && is_array($request->porcentajes)) {
                $orden = 1;
                foreach ($request->porcentajes as $porcentajeData) {
                    $tipo = $porcentajeData['tipo_porcentaje'] ?? null;
                    $modo = $porcentajeData['modo_comision'] ?? 'porcentaje';
                    $cantP = $porcentajeData['cantidad_porcentaje'] ?? null;
                    $montoF = $porcentajeData['monto_fijo'] ?? null;

                    // Asegurar valores por defecto para evitar errores de SQL (null en columnas no nulas)
                    $porcentajeData['cantidad_porcentaje'] = $cantP ?: 0;
                    $porcentajeData['monto_fijo'] = $montoF ?: 0;
                    $porcentajeData['orden'] = $orden++;

                    // Verificar que tenga tipo y al menos uno de los valores
                    if (!empty($tipo) && (($modo === 'porcentaje' && $cantP !== null && $cantP !== '') || ($modo === 'monto' && $montoF !== null && $montoF !== ''))) {
                        $porcentajeData['paquete_id'] = $paquete->id;
                        Porcentaje::create($porcentajeData);
                    }
                }
            }

            DB::commit();

            return Redirect::route('paquetes.index')
                ->with('success', 'Paquete creado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()
                ->with('error', 'Error al crear el paquete: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $paquete = Paquete::with(['porcentajes', 'contratos.cliente', 'contratos.pagos'])->find($id);

        return view('paquete.show', compact('paquete'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $paquete = Paquete::with('porcentajes')->find($id);

        return view('paquete.edit', compact('paquete'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PaqueteRequest $request, $id): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $paquete = Paquete::findOrFail($id);

            // Actualizar el paquete
            $paquete->update($request->validated());

            // Eliminar porcentajes existentes
            $paquete->porcentajes()->delete();

            // Crear los nuevos porcentajes solo si existen
            if ($request->has('porcentajes') && is_array($request->porcentajes)) {
                $orden = 1;
                foreach ($request->porcentajes as $porcentajeData) {
                    $tipo = $porcentajeData['tipo_porcentaje'] ?? null;
                    $modo = $porcentajeData['modo_comision'] ?? 'porcentaje';
                    $cantP = $porcentajeData['cantidad_porcentaje'] ?? null;
                    $montoF = $porcentajeData['monto_fijo'] ?? null;

                    // Asegurar valores por defecto para evitar errores de SQL (null en columnas no nulas)
                    $porcentajeData['cantidad_porcentaje'] = $cantP ?: 0;
                    $porcentajeData['monto_fijo'] = $montoF ?: 0;
                    $porcentajeData['orden'] = $orden++;

                    if (!empty($tipo) && (($modo === 'porcentaje' && $cantP !== null && $cantP !== '') || ($modo === 'monto' && $montoF !== null && $montoF !== ''))) {
                        $porcentajeData['paquete_id'] = $paquete->id;
                        Porcentaje::create($porcentajeData);
                    }
                }
            }

            DB::commit();

            return Redirect::route('paquetes.index')
                ->with('success', 'Paquete modificado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()
                ->with('error', 'Error al actualizar el paquete: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $paquete = Paquete::find($id);
            
            // Eliminar porcentajes asociados
            $paquete->porcentajes()->delete();
            
            // Eliminar el paquete
            $paquete->delete();

            DB::commit();

            return Redirect::route('paquetes.index')
                ->with('success', 'Paquete y porcentajes eliminados correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::route('paquetes.index')
                ->with('error', 'Error al eliminar el paquete: ' . $e->getMessage());
        }
    }
}
