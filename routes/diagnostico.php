<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

/**
 * RUTAS DE DIAGNÓSTICO - SOLO PARA ADMINISTRADORES
 * Eliminar o comentar en producción después de diagnosticar
 */

Route::middleware(['auth', 'role:admin'])->group(function () {
    
    // Diagnóstico general del sistema
    Route::get('/diagnostico', function () {
        try {
            $diagnostico = [
                'Laravel Version' => app()->version(),
                'PHP Version' => PHP_VERSION,
                'Environment' => config('app.env'),
                'Debug Mode' => config('app.debug') ? 'ACTIVADO' : 'DESACTIVADO',
                'URL' => config('app.url'),
                'Database' => [
                    'Connection' => config('database.default'),
                    'Host' => config('database.connections.mysql.host'),
                    'Database' => config('database.connections.mysql.database'),
                    'Status' => 'Intentando conectar...',
                ],
                'Cache' => [
                    'Driver' => config('cache.default'),
                ],
                'Session' => [
                    'Driver' => config('session.driver'),
                ],
            ];

            // Probar conexión a BD
            try {
                DB::connection()->getPdo();
                $diagnostico['Database']['Status'] = '✅ CONECTADO';
                $diagnostico['Database']['Version'] = DB::select('SELECT VERSION() as version')[0]->version;
            } catch (\Exception $e) {
                $diagnostico['Database']['Status'] = '❌ ERROR: ' . $e->getMessage();
            }

            // Verificar permisos
            $diagnostico['Permissions'] = [
                'storage writable' => is_writable(storage_path()) ? '✅ SI' : '❌ NO',
                'bootstrap/cache writable' => is_writable(base_path('bootstrap/cache')) ? '✅ SI' : '❌ NO',
            ];

            // Último error del log
            $logFile = storage_path('logs/laravel.log');
            if (file_exists($logFile)) {
                $lines = file($logFile);
                $lastLines = array_slice($lines, -50);
                $diagnostico['Last 50 Log Lines'] = implode('', $lastLines);
            }

            return response()->json($diagnostico, 200, [], JSON_PRETTY_PRINT);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500, [], JSON_PRETTY_PRINT);
        }
    });

    // Limpiar cachés desde navegador
    Route::get('/diagnostico/clear-cache', function () {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            
            return response()->json([
                'success' => true,
                'message' => 'Cachés limpiados correctamente',
                'commands_executed' => [
                    'cache:clear',
                    'config:clear',
                    'route:clear',
                    'view:clear',
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    });

    // Probar vista de empleado específica con debugging
    Route::get('/diagnostico/empleado/{id}', function ($id) {
        try {
            $empleado = \App\Models\Empleado::with('user')->findOrFail($id);
            
            return response()->json([
                'empleado' => [
                    'id' => $empleado->id,
                    'nombre' => $empleado->nombre,
                    'apellido' => $empleado->apellido,
                    'user_exists' => $empleado->user ? 'SI' : 'NO',
                    'user_id' => $empleado->user_id,
                    'user_email' => $empleado->user?->email,
                    'user_role' => $empleado->user?->role,
                    'created_at' => $empleado->created_at->toDateTimeString(),
                ],
                'comisiones_count' => $empleado->comisiones()->count(),
            ], 200, [], JSON_PRETTY_PRINT);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500, [], JSON_PRETTY_PRINT);
        }
    });

    // Ver logs en tiempo real
    Route::get('/diagnostico/logs', function () {
        $logFile = storage_path('logs/laravel.log');
        
        if (!file_exists($logFile)) {
            return response('No hay archivo de logs', 404);
        }
        
        $lines = file($logFile);
        $lastLines = array_slice($lines, -200); // Últimas 200 líneas
        
        return response('<pre>' . htmlspecialchars(implode('', $lastLines)) . '</pre>')
            ->header('Content-Type', 'text/html');
    });
});
