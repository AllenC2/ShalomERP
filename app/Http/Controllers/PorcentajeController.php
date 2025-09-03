<?php

namespace App\Http\Controllers;

use App\Models\Porcentaje;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\PorcentajeRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class PorcentajeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $porcentajes = Porcentaje::paginate();

        return view('porcentaje.index', compact('porcentajes'))
            ->with('i', ($request->input('page', 1) - 1) * $porcentajes->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $porcentaje = new Porcentaje();

        return view('porcentaje.create', compact('porcentaje'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PorcentajeRequest $request): RedirectResponse
    {
        Porcentaje::create($request->validated());

        return Redirect::route('porcentajes.index')
            ->with('success', 'Porcentaje created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $porcentaje = Porcentaje::find($id);

        return view('porcentaje.show', compact('porcentaje'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $porcentaje = Porcentaje::find($id);

        return view('porcentaje.edit', compact('porcentaje'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PorcentajeRequest $request, Porcentaje $porcentaje): RedirectResponse
    {
        $porcentaje->update($request->validated());

        return Redirect::route('porcentajes.index')
            ->with('success', 'Porcentaje updated successfully');
    }

    public function destroy($id): RedirectResponse
    {
        Porcentaje::find($id)->delete();

        return Redirect::route('porcentajes.index')
            ->with('success', 'Porcentaje deleted successfully');
    }
}
