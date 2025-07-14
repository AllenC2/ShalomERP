<?php

namespace App\Http\Controllers;

use App\Models\Comisione;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\ComisioneRequest;
use Illuminate\Support\Facades\Redirect;
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

        return view('comisione.create', compact('comisione'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ComisioneRequest $request): RedirectResponse
    {
        Comisione::create($request->validated());

        return Redirect::route('comisiones.index')
            ->with('success', 'Comisione created successfully.');
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

        return view('comisione.edit', compact('comisione'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ComisioneRequest $request, Comisione $comisione): RedirectResponse
    {
        $comisione->update($request->validated());

        return Redirect::route('comisiones.index')
            ->with('success', 'Comisione updated successfully');
    }

    public function destroy($id): RedirectResponse
    {
        Comisione::find($id)->delete();

        return Redirect::route('comisiones.index')
            ->with('success', 'Comisione deleted successfully');
    }
}
