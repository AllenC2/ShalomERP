<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PagoController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware(['auth'])->group(function () {
    // Rutas para administradores
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('empleados', App\Http\Controllers\EmpleadoController::class);
        Route::resource('comisiones', App\Http\Controllers\ComisioneController::class);
        Route::resource('paquetes', App\Http\Controllers\PaqueteController::class);
    });
    
    // Rutas para administradores y vendedores
    Route::middleware(['role:admin,vendedor'])->group(function () {
        Route::resource('clientes', App\Http\Controllers\ClienteController::class);
        Route::resource('contratos', App\Http\Controllers\ContratoController::class);
        Route::resource('pagos', App\Http\Controllers\PagoController::class);
        
        Route::patch('pagos/{id}/toggle-estado', [PagoController::class, 'toggleEstado'])->name('pagos.toggleEstado');
        Route::patch('contratos/{id}/cancel', [App\Http\Controllers\ContratoController::class, 'cancel'])->name('contratos.cancel');
        Route::get('contratos/{id}/comisiones', [App\Http\Controllers\ContratoController::class, 'comisiones'])->name('contratos.comisiones');
    });
});

