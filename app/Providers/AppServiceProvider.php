<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        // Configurar Carbon en español
        Carbon::setLocale(config('app.locale'));

        // Intentar configurar el locale del sistema (no crítico si falla)
        @setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'es', 'Spanish_Spain', 'spanish');

        // Restringir acceso al Log Viewer solo a administradores
        \Opcodes\LogViewer\Facades\LogViewer::auth(function ($request) {
            return $request->user() && $request->user()->role === 'admin';
        });
    }
}
