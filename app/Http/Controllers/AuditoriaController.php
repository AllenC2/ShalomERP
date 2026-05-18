<?php

namespace App\Http\Controllers;

use App\Models\LogAuditoria;
use Illuminate\Http\Request;

class AuditoriaController extends Controller
{
    /**
     * Helper to verify secondary audit authentication status.
     * Enforces a rolling 15-minute verification session.
     */
    private function checkSecondaryAuth()
    {
        $verifiedUntil = session('audit_verified_until');
        
        if ($verifiedUntil && now()->lessThan($verifiedUntil)) {
            // Extend session validation for another 15 minutes of activity
            session(['audit_verified_until' => now()->addMinutes(15)]);
            return true;
        }

        // Session expired or not present
        session()->forget(['audit_verified_until']);
        return false;
    }

    /**
     * Render the secondary password challenge screen.
     */
    public function showVerifyForm()
    {
        // If already verified, redirect straight to the index
        if ($this->checkSecondaryAuth()) {
            return redirect()->route('auditoria.index');
        }

        return view('auditoria.verify');
    }

    /**
     * Validate the secondary password and set the secure session.
     */
    public function verifyPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        // Secure secondary password loaded from environment variables
        // Defaults to 'ShalomAudit2026!' if not configured in .env
        $configuredPassword = env('AUDIT_PANEL_PASSWORD', 'ShalomAudit2026!');

        if ($request->input('password') === $configuredPassword) {
            // Store rolling active time limit (15 minutes)
            session(['audit_verified_until' => now()->addMinutes(15)]);
            
            return redirect()->route('auditoria.index')->with('success', 'Acceso a Bitácora Forense Autorizado.');
        }

        return redirect()->route('auditoria.verify.form')
            ->withErrors(['password' => 'Clave de seguridad secundaria incorrecta. Acceso denegado.']);
    }

    /**
     * Display a listing of the audit logs (Secured via secondary authentication).
     */
    public function index(Request $request)
    {
        if (!$this->checkSecondaryAuth()) {
            return redirect()->route('auditoria.verify.form');
        }

        $query = LogAuditoria::with('usuario')->orderBy('id', 'desc');

        // Filtrar por Nombre de Tabla
        if ($request->filled('tabla')) {
            $query->where('tabla_nombre', 'like', '%' . $request->input('tabla') . '%');
        }

        // Filtrar por Acción (INSERT, UPDATE, DELETE)
        if ($request->filled('accion')) {
            $query->where('accion', $request->input('accion'));
        }

        // Filtrar por Operador (usuario_id o nombre)
        if ($request->filled('usuario')) {
            $usuarioBusqueda = $request->input('usuario');
            $query->whereHas('usuario', function ($q) use ($usuarioBusqueda) {
                $q->where('name', 'like', '%' . $usuarioBusqueda . '%')
                  ->orWhere('email', 'like', '%' . $usuarioBusqueda . '%');
            });
        }

        // Filtrar por IP
        if ($request->filled('ip')) {
            $query->where('ip_direccion', 'like', '%' . $request->input('ip') . '%');
        }

        // Filtrar por Rango de Fechas
        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->input('fecha_desde'));
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->input('fecha_hasta'));
        }

        // Paginamos para optimizar tiempos de carga
        $logs = $query->paginate(25)->withQueryString();

        return view('auditoria.index', compact('logs'));
    }

    /**
     * Display the specified audit log detail (Secured via secondary authentication).
     */
    public function show($id)
    {
        if (!$this->checkSecondaryAuth()) {
            return response()->json(['error' => 'No autorizado. Requiere verificación secundaria.'], 403);
        }

        $log = LogAuditoria::with('usuario')->findOrFail($id);

        return response()->json($log);
    }
}
