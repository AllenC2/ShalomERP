<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\EmpleadoRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class EmpleadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $empleados = Empleado::with('user')->paginate();

        return view('empleado.index', compact('empleados'))
            ->with('i', ($request->input('page', 1) - 1) * $empleados->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $empleado = new Empleado();

        return view('empleado.create', compact('empleado'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EmpleadoRequest $request): RedirectResponse
    {
        // Crear el usuario primero
        $user = User::create([
            'name' => $request->nombre . ' ' . $request->apellido,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'empleado'
        ]);

        // Crear el empleado vinculado al usuario
        $empleado = Empleado::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'user_id' => $user->id,
            'telefono' => $request->telefono,
            'domicilio' => $request->domicilio,
        ]);

        return Redirect::route('empleados.index')
            ->with('success', 'Empleado creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $empleado = Empleado::with('user')->findOrFail($id);
        $comisiones = $empleado->comisiones()->with(['contrato.cliente', 'contrato.paquete', 'parcialidades'])->orderBy('fecha_comision', 'desc')->get();

        return view('empleado.show', compact('empleado', 'comisiones'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $empleado = Empleado::with('user')->findOrFail($id);

        return view('empleado.edit', compact('empleado'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EmpleadoRequest $request, Empleado $empleado): RedirectResponse
    {
        // Actualizar datos del empleado
        $empleado->update([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'telefono' => $request->telefono,
            'domicilio' => $request->domicilio,
        ]);

        // Actualizar datos del usuario asociado
        $empleado->user->update([
            'name' => $request->nombre . ' ' . $request->apellido,
            'email' => $request->email,
        ]);

        return Redirect::route('empleados.index')
            ->with('success', 'Empleado modificado correctamente.');
    }

    public function destroy($id): RedirectResponse
    {
        $empleado = Empleado::findOrFail($id);
        
        // Eliminar el usuario asociado (esto eliminará en cascada el empleado)
        $empleado->user->delete();

        return Redirect::route('empleados.index')
            ->with('success', 'Empleado eliminado correctamente.');
    }

    /**
     * Dar de baja a un empleado (cambiar estado a inactivo)
     */
    public function darDeBaja($id): RedirectResponse
    {
        $empleado = Empleado::find($id);
        
        if (!$empleado) {
            return Redirect::route('empleados.index')
                ->with('error', 'Empleado no encontrado.');
        }

        $empleado->update(['estado' => 'inactivo']);

        return Redirect::route('empleados.show', $id)
            ->with('success', 'Empleado dado de baja correctamente.');
    }

    /**
     * Reactivar a un empleado (cambiar estado a activo)
     */
    public function reactivar($id): RedirectResponse
    {
        $empleado = Empleado::find($id);
        
        if (!$empleado) {
            return Redirect::route('empleados.index')
                ->with('error', 'Empleado no encontrado.');
        }

        $empleado->update(['estado' => 'activo']);

        return Redirect::route('empleados.show', $id)
            ->with('success', 'Empleado reactivado correctamente.');
    }

    /**
     * Cambiar el rol del usuario entre admin y empleado
     */
    public function toggleRol($id): RedirectResponse
    {
        $empleado = Empleado::with('user')->find($id);
        
        if (!$empleado) {
            return Redirect::route('empleados.index')
                ->with('error', 'Empleado no encontrado.');
        }

        $user = $empleado->user;
        
        // Cambiar el rol
        $nuevoRol = $user->role === 'admin' ? 'empleado' : 'admin';
        $user->update(['role' => $nuevoRol]);

        $mensaje = $nuevoRol === 'admin' 
            ? 'El empleado ahora tiene rol de Administrador.' 
            : 'El empleado ahora tiene rol de Empleado.';

        return Redirect::route('empleados.show', $id)
            ->with('success', $mensaje);
    }
}
