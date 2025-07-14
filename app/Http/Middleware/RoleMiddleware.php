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
        if (!in_array($user->role, $roles)) {
            abort(403, 'No tienes permisossss para acceder a esta sección');
        }
        
        return $next($request);
    }
}