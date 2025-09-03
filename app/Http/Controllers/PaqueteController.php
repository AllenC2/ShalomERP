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

        return view('paquete.index', compact('paquetes'))
            ->with('i', ($request->input('page', 1) - 1) * $paquetes->perPage());
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
                foreach ($request->porcentajes as $porcentajeData) {
                    // Verificar que los datos requeridos estén presentes
                    if (!empty($porcentajeData['cantidad_porcentaje']) && !empty($porcentajeData['tipo_porcentaje'])) {
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
    public function update(PaqueteRequest $request, Paquete $paquete): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Actualizar el paquete
            $paquete->update($request->validated());

            // Eliminar porcentajes existentes
            $paquete->porcentajes()->delete();

            // Crear los nuevos porcentajes solo si existen
            if ($request->has('porcentajes') && is_array($request->porcentajes)) {
                foreach ($request->porcentajes as $porcentajeData) {
                    // Verificar que los datos requeridos estén presentes
                    if (!empty($porcentajeData['cantidad_porcentaje']) && !empty($porcentajeData['tipo_porcentaje'])) {
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
