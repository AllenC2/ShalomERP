<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\PagoRequest;
use Illuminate\Support\Facades\Redirect;
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
        if ($estado && in_array($estado, ['Hecho', 'Pendiente'])) {
            $pagosQuery->where('estado', $estado);
        }
        $pagos = $pagosQuery->paginate();

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

        return view('pago.create', compact('pago', 'contrato_id'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PagoRequest $request): RedirectResponse
    {
        Pago::create($request->validated());

        return Redirect::back()
            ->with('success', 'Pago created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $pago = Pago::find($id);

        return view('pago.show', compact('pago'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $pago = Pago::find($id);

        return view('pago.edit', compact('pago'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PagoRequest $request, Pago $pago): RedirectResponse
    {
        $pago->update($request->validated());

        return Redirect::route('contratos.show', $pago->contrato_id)
            ->with('success', 'Pago updated successfully');
    }

    public function destroy($id): RedirectResponse
    {
        Pago::find($id)->delete();

        return Redirect::route('pagos.index')
            ->with('success', 'Pago deleted successfully');
    }

}
