<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DynamicSessionConfig
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        
        config([
            'session.cookie' => 'shalom_erp_' . md5($host) . '_session',
            'session.domain' => null,
        ]);
        
        return $next($request);
    }
}
