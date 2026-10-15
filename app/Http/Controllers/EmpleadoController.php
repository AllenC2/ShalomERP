<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\RutaActividad;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\EmpleadoRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EmpleadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $sort_by = $request->input('sort_by', 'created_at');
        $sort_dir = $request->input('sort_dir', 'desc');

        $query = Empleado::with('user');

        // Validar columnas para evitar SQL Injection
        $columnasValidas = ['id', 'nombre', 'rol', 'telefono', 'created_at'];
        if (in_array($sort_by, $columnasValidas)) {
            $query->orderBy($sort_by, $sort_dir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $empleados = $query->paginate()->appends($request->all());

        return view('empleado.index', compact('empleados', 'sort_by', 'sort_dir'))
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

        // Crear el empleado vinculado al usuario con ID personalizado
        $empleado = Empleado::create([
            'id' => $request->id,
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
        try {
            $empleado = Empleado::with(['user', 'plantillas'])->findOrFail($id);

            // Verificar que el empleado tenga un usuario asociado
            if (!$empleado->user) {
                return view('empleado.show', [
                    'empleado' => null,
                    'comisiones' => collect([]),
                    'plantillasAsignadas' => collect([]),
                ]);
            }

            $comisiones = $empleado->comisiones()
                ->with(['contrato.cliente', 'contrato.paquete', 'parcialidades'])
                ->orderBy('fecha_comision', 'desc')
                ->get();

            $plantillasAsignadas = $empleado->plantillas
                ->sortBy('nombre')
                ->values();

            return view('empleado.show', compact('empleado', 'comisiones', 'plantillasAsignadas'));
        } catch (\Exception $e) {
            \Log::error('Error en empleado.show: ' . $e->getMessage());

            return view('empleado.show', [
                'empleado' => null,
                'comisiones' => collect([]),
                'plantillasAsignadas' => collect([]),
            ])->with('error', 'Error al cargar el empleado: ' . $e->getMessage());
        }
    }

    /**
     * Historial de movimientos de rutas del empleado (solo admin).
     */
    public function actividadRutas($id): JsonResponse
    {
        $empleado = Empleado::findOrFail($id);

        $actividades = RutaActividad::query()
            ->where('empleado_id', $empleado->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(500)
            ->get();

        $grupos = $actividades
            ->groupBy(fn (RutaActividad $a) => optional($a->created_at)->format('Y-m') ?: 'sin-fecha')
            ->map(function ($items, $ym) {
                $fecha = $items->first()?->created_at;
                $etiqueta = $fecha
                    ? ucfirst($fecha->translatedFormat('F Y'))
                    : 'Sin fecha';

                return [
                    'key' => $ym,
                    'label' => $etiqueta,
                    'items' => $items->map(fn (RutaActividad $a) => [
                        'id' => $a->id,
                        'tipo' => $a->tipo,
                        'tipo_label' => $a->etiquetaTipo(),
                        'badge' => $a->badgeClass(),
                        'titulo' => $a->titulo,
                        'detalle' => $a->detalle,
                        'monto_total' => $a->monto_total !== null ? (float) $a->monto_total : null,
                        'ruta_nombre' => $a->meta['ruta_nombre'] ?? null,
                        'fecha' => optional($a->created_at)->format('d/m/Y'),
                        'hora' => optional($a->created_at)->format('H:i'),
                        'created_at' => optional($a->created_at)->toIso8601String(),
                    ])->values(),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'empleado' => [
                'id' => $empleado->id,
                'nombre' => trim($empleado->nombre.' '.$empleado->apellido),
            ],
            'total' => $actividades->count(),
            'grupos' => $grupos,
        ]);
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
        // Si el ID cambió, necesitamos actualizar manualmente
        if ($empleado->id !== $request->id) {
            // Verificar que el nuevo ID no exista
            if (Empleado::where('id', $request->id)->exists()) {
                return Redirect::back()
                    ->withErrors(['id' => 'El ID ya existe.'])
                    ->withInput();
            }

            // Crear un nuevo registro con el nuevo ID
            $nuevoEmpleado = new Empleado();
            $nuevoEmpleado->id = $request->id;
            $nuevoEmpleado->nombre = $request->nombre;
            $nuevoEmpleado->apellido = $request->apellido;
            $nuevoEmpleado->user_id = $empleado->user_id;
            $nuevoEmpleado->telefono = $request->telefono;
            $nuevoEmpleado->domicilio = $request->domicilio;
            $nuevoEmpleado->latitud = $request->latitud;
            $nuevoEmpleado->longitud = $request->longitud;
            $nuevoEmpleado->estado = $empleado->estado;
            $nuevoEmpleado->save();

            // Actualizar las referencias en comisiones
            \DB::table('comisiones')->where('empleado_id', $empleado->id)->update(['empleado_id' => $request->id]);

            // Eliminar el registro antiguo
            $empleado->delete();

            // Actualizar datos del usuario asociado
            $nuevoEmpleado->user->update([
                'name' => $request->nombre . ' ' . $request->apellido,
                'email' => $request->email,
            ]);
        } else {
            // Actualizar datos del empleado normalmente si el ID no cambió
            $empleado->update([
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'telefono' => $request->telefono,
                'domicilio' => $request->domicilio,
                'latitud' => $request->latitud,
                'longitud' => $request->longitud,
            ]);

            // Actualizar datos del usuario asociado
            $empleado->user->update([
                'name' => $request->nombre . ' ' . $request->apellido,
                'email' => $request->email,
            ]);
        }

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
     * e invalidar sesiones / remember token de su usuario.
     */
    public function darDeBaja($id): RedirectResponse
    {
        $empleado = Empleado::with(['user', 'plantillas'])->find($id);

        if (!$empleado) {
            return Redirect::route('empleados.index')
                ->with('error', 'Empleado no encontrado.');
        }

        $plantillas = $empleado->plantillas;
        if ($plantillas->isNotEmpty()) {
            $nombres = $plantillas
                ->map(fn ($p) => $p->nombre ?: ('Ruta #' . $p->id))
                ->take(5)
                ->implode(', ');
            $extra = $plantillas->count() > 5 ? ' y ' . ($plantillas->count() - 5) . ' más' : '';

            return Redirect::route('empleados.show', $id)
                ->with('error', 'No se puede dar de baja: este empleado tiene '
                    . $plantillas->count() . ' ruta(s) asignada(s) (' . $nombres . $extra
                    . '). Reasigna o elimina esas rutas antes de continuar.');
        }

        $empleado->update(['estado' => 'inactivo']);
        $this->invalidarAccesoUsuario($empleado->user);

        return Redirect::route('empleados.show', $id)
            ->with('success', 'Empleado dado de baja correctamente. Ya no puede acceder al sistema.');
    }

    /**
     * Cierra sesiones activas y olvida el "recordarme" del usuario.
     */
    protected function invalidarAccesoUsuario(?User $user): void
    {
        if (! $user) {
            return;
        }

        $user->setRememberToken(Str::random(60));
        $user->save();

        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $user->id)
                ->delete();
        }
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

    /**
     * Cambiar la contraseña del usuario asociado al empleado
     */
    public function cambiarContrasena(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $empleado = Empleado::with('user')->findOrFail($id);

        if (!$empleado->user) {
            return Redirect::back()->with('error', 'El empleado no tiene un usuario asociado.');
        }

        $empleado->user->update([
            'password' => Hash::make($request->password)
        ]);

        return Redirect::back()->with('success', 'Contraseña actualizada correctamente.');
    }
}
