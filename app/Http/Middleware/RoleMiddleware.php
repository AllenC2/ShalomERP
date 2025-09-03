<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        
        // Verificar si el usuario está autenticado
        if (!$user) {
            abort(401, 'No autorizado');
        }
        
        // Verificar si el usuario tiene alguno de los roles permitidos
        // Devolvemos 404 en lugar de 403 para ocultar la existencia del recurso
        if (!in_array($user->role, $roles)) {
            abort(404);
        }
        
        return $next($request);
    }
}