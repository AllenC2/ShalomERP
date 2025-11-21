<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        then: function () {
            // Rutas de diagnóstico (solo para admin)
            if (file_exists(__DIR__.'/../routes/diagnostico.php')) {
                require __DIR__.'/../routes/diagnostico.php';
            }
        },
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'empleado.index.access' => \App\Http\Middleware\EmpleadoContratoAccess::class,
        ]);
        
        // Aplicar middleware de registro público a todas las rutas web
        $middleware->web(append: [
            \App\Http\Middleware\CheckRegistroPublico::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Renderizador personalizado para mantener autenticación en páginas de error
        $exceptions->render(function (\Throwable $e, $request) {
            // Solo aplicar para errores HTTP y solicitudes web (no JSON/API)  
            if (!$request->expectsJson() && $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                $statusCode = $e->getStatusCode();
                
                // Buscar usuario autenticado usando todos los métodos posibles
                $user = null;
                $isAuthenticated = false;
                
                try {
                    $session = $request->session();
                    if ($session) {
                        // Obtener todos los datos de sesión para buscar claves de autenticación
                        $sessionData = $session->all();
                        
                        // Buscar claves que contengan información de autenticación
                        foreach ($sessionData as $key => $value) {
                            // Buscar claves típicas de autenticación de Laravel
                            if (str_contains($key, 'login') || str_contains($key, 'auth') || str_contains($key, 'user')) {
                                if (is_numeric($value)) {
                                    $foundUser = \App\Models\User::find($value);
                                    if ($foundUser) {
                                        $user = $foundUser;
                                        $isAuthenticated = true;
                                        break;
                                    }
                                }
                            }
                        }
                        
                        // Si no encontramos nada, usar la clave estándar de Laravel
                        if (!$user) {
                            $authKey = 'login_web_' . sha1('web');
                            if ($session->has($authKey)) {
                                $userId = $session->get($authKey);
                                if ($userId) {
                                    $user = \App\Models\User::find($userId);
                                    if ($user) {
                                        $isAuthenticated = true;
                                    }
                                }
                            }
                        }
                    }
                    
                    // Último recurso: verificar cookies remember_token
                    if (!$user && $request->hasCookie('remember_web_' . sha1(\Illuminate\Support\Str::class))) {
                        $recaller = $request->cookie('remember_web_' . sha1(\Illuminate\Support\Str::class));
                        if ($recaller) {
                            $segments = explode('|', $recaller);
                            if (count($segments) >= 2) {
                                $userId = $segments[0];
                                $user = \App\Models\User::find($userId);
                                if ($user) {
                                    $isAuthenticated = true;
                                }
                            }
                        }
                    }
                    
                } catch (\Exception $authException) {
                    // Si hay cualquier error, continuar sin usuario
                    $user = null;
                    $isAuthenticated = false;
                }
                
                // Datos para la vista
                $data = [
                    'exception' => $e,
                    'currentUser' => $user,
                    'isAuthenticated' => $isAuthenticated
                ];
                
                // Verificar si existe una vista específica para el código de error
                if (view()->exists("errors.{$statusCode}")) {
                    return response()->view("errors.{$statusCode}", $data, $statusCode);
                }
                
                // Usar la vista genérica de error si no existe una específica
                if (view()->exists('errors.error')) {
                    return response()->view('errors.error', $data, $statusCode);
                }
            }
            
            return null;
        });
    })->create();
