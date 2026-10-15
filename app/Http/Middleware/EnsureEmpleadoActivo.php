<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmpleadoActivo
{
    /**
     * Cierra la sesión si el usuario autenticado está ligado a un empleado inactivo.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->estaDadoDeBaja()) {
            Auth::logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Tu cuenta está dada de baja. Contacta al administrador.',
                ], 401);
            }

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Tu cuenta está dada de baja. Contacta al administrador.',
                ]);
        }

        return $next($request);
    }
}
