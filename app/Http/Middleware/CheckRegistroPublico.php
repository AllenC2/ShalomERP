<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Ajuste;

class CheckRegistroPublico
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Solo verificar en la ruta de registro
        if ($request->is('register') || $request->is('register/*')) {
            try {
                $registroPublico = Ajuste::obtener('registro_publico_activo', false);
                
                if (!$registroPublico) {
                    return redirect('/login')->with('error', 'El registro público no está disponible en este momento.');
                }
            } catch (\Exception $e) {
                return redirect('/login')->with('error', 'El registro público no está disponible en este momento.');
            }
        }
        
        return $next($request);
    }
}
