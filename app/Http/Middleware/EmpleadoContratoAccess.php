<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmpleadoContratoAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        // Verificar si el usuario está autenticado
        if (!$user) {
            abort(401, 'No autorizado');
        }
        
        // Si es admin, permitir acceso completo
        if ($user->role === 'admin') {
            return $next($request);
        }
        
        // Si es empleado y está intentando acceder al index de contratos, clientes o pagos, denegar acceso
        if ($user->role === 'empleado' && 
            ($request->route()->getName() === 'contratos.index' || 
             $request->route()->getName() === 'clientes.index' ||
             $request->route()->getName() === 'pagos.index')) {
            abort(404); // Devolvemos 404 para ocultar la existencia del recurso
        }
        
        return $next($request);
    }
}
