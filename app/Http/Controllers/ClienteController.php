<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Contrato;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\ClienteRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $busqueda = $request->input('busqueda');
        $clientes = Cliente::query();

        if ($busqueda) {
            $clientes->where(function($query) use ($busqueda) {
                $query->where('nombre', 'like', "%$busqueda%")
                      ->orWhere('apellido', 'like', "%$busqueda%")
                      ->orWhere('email', 'like', "%$busqueda%")
                      ->orWhere('telefono', 'like', "%$busqueda%")
                      ->orWhere('domicilio_completo', 'like', "%$busqueda%")
                      ->orWhere('colonia', 'like', "%$busqueda%")
                      ->orWhere('municipio', 'like', "%$busqueda%");
            });
        }

        // Cargar el conteo de contratos activos
        $clientes->withCount([
            'contratos as contratos_activos_count' => function ($query) {
                $query->where('estado', 'activo');
            }
        ]);

        $clientes = $clientes->paginate();

        return view('cliente.index', compact('clientes'))
            ->with('i', ($request->input('page', 1) - 1) * $clientes->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $cliente = new Cliente();

        return view('cliente.create', compact('cliente'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ClienteRequest $request): RedirectResponse
    {
        Cliente::create($request->validated());

        return Redirect::route('clientes.index')
            ->with('success', 'Cliente creado correctamente..');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $cliente = Cliente::find($id);
        $cliente_contratos = Contrato::where('cliente_id', $id)->get();
        
        return view('cliente.show', compact('cliente', 'cliente_contratos'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $cliente = Cliente::find($id);

        return view('cliente.edit', compact('cliente'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ClienteRequest $request, Cliente $cliente): RedirectResponse
    {
        $cliente->update($request->validated());

        return Redirect::route('clientes.index')
            ->with('success', 'Cliente modificado correctamente.');
    }

    public function destroy($id): RedirectResponse
    {
        Cliente::find($id)->delete();

        return Redirect::route('clientes.index')
            ->with('success', 'Cliente eliminado correctamente.');
    }
}
