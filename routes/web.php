<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PagoController;
use App\Models\Ajuste;

Route::get('/', function () {
    return view('welcome');
});

// Habilitar rutas de autenticación con registro
Auth::routes(['register' => true]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Rutas restringidas - Solo acceso para administradores
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Rutas de ajustes
    Route::resource('ajustes', App\Http\Controllers\AjustesController::class);
    Route::post('ajustes/empresa', [App\Http\Controllers\AjustesController::class, 'actualizarEmpresa'])->name('ajustes.empresa');
    Route::post('ajustes/recordatorio-whatsapp', [App\Http\Controllers\AjustesController::class, 'actualizarRecordatorioWhatsApp'])->name('ajustes.recordatorioWhatsApp');
    Route::post('ajustes/tolerancia-pagos', [App\Http\Controllers\AjustesController::class, 'actualizarToleranciaPagos'])->name('ajustes.toleranciaPagos');
    Route::post('ajustes/registro-publico', [App\Http\Controllers\AjustesController::class, 'actualizarRegistroPublico'])->name('ajustes.registroPublico');
    
    // Rutas de empleados
    Route::resource('empleados', App\Http\Controllers\EmpleadoController::class);
    Route::patch('empleados/{id}/dar-de-baja', [App\Http\Controllers\EmpleadoController::class, 'darDeBaja'])->name('empleados.darDeBaja');
    Route::patch('empleados/{id}/reactivar', [App\Http\Controllers\EmpleadoController::class, 'reactivar'])->name('empleados.reactivar');
    Route::patch('empleados/{id}/toggle-rol', [App\Http\Controllers\EmpleadoController::class, 'toggleRol'])->name('empleados.toggleRol');
    
    // Rutas de comisiones
    Route::resource('comisiones', App\Http\Controllers\ComisioneController::class);
    Route::patch('comisiones/{id}/toggle-estado', [App\Http\Controllers\ComisioneController::class, 'toggleEstado'])->name('comisiones.toggleEstado');
    Route::get('contratos/{contrato_id}/comisiones/estados', [App\Http\Controllers\ComisioneController::class, 'getEstadosContrato'])->name('comisiones.getEstadosContrato');
    Route::delete('comisiones/{id}/delete-documento', [App\Http\Controllers\ComisioneController::class, 'deleteDocumento'])->name('comisiones.deleteDocumento');
    Route::post('comisiones/{id}/upload-documento', [App\Http\Controllers\ComisioneController::class, 'uploadDocumento'])->name('comisiones.uploadDocumento');
    Route::delete('comisiones/{id}/eliminar-parcialidad', [App\Http\Controllers\ComisioneController::class, 'eliminarParcialidad'])->name('comisiones.eliminarParcialidad');
    
    // Rutas de paquetes y porcentajes
    Route::resource('paquetes', App\Http\Controllers\PaqueteController::class);
    Route::resource('porcentajes', App\Http\Controllers\PorcentajeController::class)->except([
        'index', 'show', 'create', 'edit'
    ]);
    
    // Rutas de clientes específicos - Solo administrador
    Route::get('clientes/{cliente}', [App\Http\Controllers\ClienteController::class, 'show'])->name('clientes.show');
    Route::get('clientes/{cliente}/edit', [App\Http\Controllers\ClienteController::class, 'edit'])->name('clientes.edit');
    Route::put('clientes/{cliente}', [App\Http\Controllers\ClienteController::class, 'update'])->name('clientes.update');
    Route::patch('clientes/{cliente}', [App\Http\Controllers\ClienteController::class, 'update']);
    Route::delete('clientes/{cliente}', [App\Http\Controllers\ClienteController::class, 'destroy'])->name('clientes.destroy');
});

// Ruta para servir PDFs de contratos - Accesible para cualquier usuario autenticado
Route::middleware(['auth'])->get('/storage/contratos/{filename}', function ($filename) {
    $path = storage_path('app/public/contratos/' . $filename);
    
    if (!file_exists($path)) {
        abort(404);
    }
    
    return response()->file($path);
})->where('filename', '.*\.pdf$');

Route::middleware(['auth', 'role:admin,empleado'])->group(function () {
    // Rutas AJAX
    Route::get('ajax/porcentajes/{paquete_id}', [App\Http\Controllers\ContratoController::class, 'getPorcentajesByPaquete'])->name('ajax.porcentajes');
    Route::post('ajax/check-contrato-id', [App\Http\Controllers\ContratoController::class, 'checkContratoId'])->name('ajax.checkContratoId');
});

// Rutas de clientes, contratos y pagos con middleware específico para empleados
Route::middleware(['auth', 'role:admin,empleado', 'empleado.index.access'])->group(function () {
    // Rutas de clientes - Solo índice y crear para empleados
    Route::get('clientes', [App\Http\Controllers\ClienteController::class, 'index'])->name('clientes.index');
    Route::get('clientes/create', [App\Http\Controllers\ClienteController::class, 'create'])->name('clientes.create');
    Route::post('clientes', [App\Http\Controllers\ClienteController::class, 'store'])->name('clientes.store');
    
    // Rutas de contratos
    Route::resource('contratos', App\Http\Controllers\ContratoController::class);
    Route::patch('contratos/{id}/cancel', [App\Http\Controllers\ContratoController::class, 'cancel'])->name('contratos.cancel');
    Route::patch('contratos/{id}/finalizar', [App\Http\Controllers\ContratoController::class, 'finalizar'])->name('contratos.finalizar');
    Route::patch('contratos/{id}/suspender', [App\Http\Controllers\ContratoController::class, 'suspender'])->name('contratos.suspender');
    Route::patch('contratos/{id}/reactivar', [App\Http\Controllers\ContratoController::class, 'reactivar'])->name('contratos.reactivar');
    Route::patch('contratos/{contrato}/observaciones', [App\Http\Controllers\ContratoController::class, 'updateObservaciones'])->name('contratos.updateObservaciones');
    Route::patch('contratos/{contrato}/documento', [App\Http\Controllers\ContratoController::class, 'updateDocumento'])->name('contratos.updateDocumento');
    Route::get('contratos/{id}/comisiones', [App\Http\Controllers\ContratoController::class, 'comisiones'])->name('contratos.comisiones');
    Route::get('contratos/{id}/estado', [App\Http\Controllers\ContratoController::class, 'estado'])->name('contratos.estado');
    Route::post('contratos/crear-parcialidad', [App\Http\Controllers\ContratoController::class, 'crearParcialidad'])->name('contratos.crearParcialidad');
    
    // Rutas de pagos
    Route::resource('pagos', App\Http\Controllers\PagoController::class);
    Route::patch('pagos/{id}/toggle-estado', [PagoController::class, 'toggleEstado'])->name('pagos.toggleEstado');
    Route::patch('pagos/{id}/metodo-pago', [PagoController::class, 'updateMetodoPago'])->name('pagos.updateMetodoPago');
    Route::post('pagos/{id}/deshacer', [PagoController::class, 'deshacerPago'])->name('pagos.deshacer');
    Route::post('pagos/{id}/upload-document', [PagoController::class, 'uploadDocument'])->name('pagos.uploadDocument');
    Route::post('pagos/{id}/upload-documento', [PagoController::class, 'uploadDocumento'])->name('pagos.uploadDocumento');
    Route::delete('pagos/{id}/delete-documento', [PagoController::class, 'deleteDocumento'])->name('pagos.deleteDocumento');
    Route::post('pagos/verificar-liquidacion-parcialidad', [PagoController::class, 'verificarLiquidacionParcialidad'])->name('pagos.verificarLiquidacionParcialidad');
    Route::get('pagos/buscar-contratos', [PagoController::class, 'buscarContratos'])->name('pagos.buscarContratos');
    
    // Ruta alternativa para pagos
    Route::get('pagos_alt', [PagoController::class, 'index'])->name('pagos_alt.index');
});

