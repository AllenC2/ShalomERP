@extends('layouts.app')

@section('template_title')
    Ajustes
@endsection

@section('content')

<div class="bg-light min-vh-100">
    <div class="container py-4">

        <h2 class="display-5 fw-bold mb-4" style="color: #79481D;">
            Ajustes Generales
        </h2>

        <!-- Header moderno -->
        @auth
        <a href="{{ route('empleados.show', Auth::user()->id) }}" class="header-text ms-3 text-decoration-none" style="color: inherit; text-align: left;">
            <div class="page-header m-0">
                <div class="header-content d-grid" style="grid-template-columns: 1fr auto; align-items: center; gap: 1.5rem; width: 100%;">
                        <div class="d-flex align-items-center">
                            <div class="header-icon d-flex align-items-center justify-content-center" style="background: linear-gradient(90deg, #E1B240 0%, #79481D 100%); color:#fff; border-radius: 50%; aspect-ratio: 1 / 1; width: 68px; font-size: 1.5rem; font-weight: 700; text-transform: uppercase;">
                                {{ collect(explode(' ', Auth::user()->name ?? 'U'))->map(fn($w) => mb_substr($w,0,1))->take(2)->join('') }}
                            </div>
                            <div class="mr-4 ms-3 text-truncate" style="min-width: 0;">
                                <h1 class="page-title mb-0" style="text-align: left;">{{ Auth::user()->name ?? 'Usuario' }}</h1>
                                <p class="text-muted mb-0" style="font-size: 0.95rem; text-align: left;">
                                    {{ Auth::user()->email ?? 'Sin correo electrónico' }}
                                </p>
                            </div>
                        </div>
                    
                    <div>
                        <span class="badge holographic-badge" style="font-size:1rem; padding:0.5em 1em;">
                            ID #{{ Auth::user()->id ?? 'N/A' }}
                        </span>
                        <style>
                            .holographic-badge {
                                background: linear-gradient(90deg, #e1b240 0%, #8ec5fc 25%, #e0c3fc 50%, #f093fb 75%, #e1b240 100%);
                                background-size: 200% 200%;
                                color: #fff;
                                border: none;
                                border-radius: 12px;
                                font-weight: 700;
                                box-shadow: 0 2px 8px rgba(225, 178, 64, 0.2);
                                animation: holo-move 3s linear infinite;
                            }
                            @keyframes holo-move {
                                0% { background-position: 0% 50%; }
                                50% { background-position: 100% 50%; }
                                100% { background-position: 0% 50%; }
                            }
                        </style>
                    </div>
                </div>
            </div>
        </a>
        @else
        <div class="alert alert-info mb-4">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Modo de prueba:</strong> No hay usuario autenticado. En producción, esta página requiere autenticación.
        </div>
        @endauth

        <!-- Alertas -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Navegación Rápida Moderna -->
        <div class="navigation-cards mb-5">
            <!-- Una sola fila con los cuatro botones -->
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <a href="{{ route('empleados.index') }}" class="nav-card text-decoration-none">
                        <div class="nav-card-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="nav-card-content">
                            <h6 class="nav-card-title">Empleados</h6>
                            <p class="nav-card-subtitle">Gestión de personal</p>
                        </div>
                        <div class="nav-card-arrow">
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6">
                    <a href="{{ route('paquetes.index') }}" class="nav-card text-decoration-none">
                        <div class="nav-card-icon">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div class="nav-card-content">
                            <h6 class="nav-card-title">Paquetes</h6>
                            <p class="nav-card-subtitle">Catálogo de paquetes</p>
                        </div>
                        <div class="nav-card-arrow">
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6">
                    <a href="{{ route('pagos.index') }}" class="nav-card text-decoration-none">
                        <div class="nav-card-icon">
                            <i class="bi bi-credit-card"></i>
                        </div>
                        <div class="nav-card-content">
                            <h6 class="nav-card-title">Pagos</h6>
                            <p class="nav-card-subtitle">Gestión de pagos</p>
                        </div>
                        <div class="nav-card-arrow">
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6">
                    <a href="{{ route('comisiones.index') }}" class="nav-card text-decoration-none">
                        <div class="nav-card-icon">
                            <i class="bi bi-percent"></i>
                        </div>
                        <div class="nav-card-content">
                            <h6 class="nav-card-title">Comisiones</h6>
                            <p class="nav-card-subtitle">Gestión de comisiones</p>
                        </div>
                        <div class="nav-card-arrow">
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Tarjeta de Información de la Empresa -->
        <div class="enterprise-info-section mb-5 w-100" style="margin-right: 0; margin-left: 0; max-width: 100%;">
            <div class="card modern-card">
                <div class="card-header" style="background: #fff;">
                    <div class="header-content">
                        <div class="header-icon" style="background: linear-gradient(90deg, #E1B240 0%, #79481D 100%); color:#fff;">
                            <i class="bi bi-bank"></i>
                        </div>
                        <div class="header-text">
                            <h4 class="card-title mb-1">Información de la Empresa</h4>
                            <p class="card-subtitle text-muted mb-0">Esta información se imprimirá en todos los recibos y facturas</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form id="empresa-form" action="{{ route('ajustes.empresa') }}" method="POST">
                        @csrf
                        <div class="row g-4">
                            <!-- Información básica y contacto (columna izquierda) -->
                            <div class="col-md-6">
                                <!-- Información básica -->
                                <div>
                                    <h6 class="section-subtitle">
                                        <i class="bi bi-info-circle me-2"></i>Información Básica
                                    </h6>
                                    <div class="mb-3">
                                        <label for="razon_social" class="form-label">
                                            Razón Social *
                                        </label>
                                        <input type="text" class="form-control modern-input" id="razon_social" name="razon_social" 
                                               value="{{ old('razon_social', isset($infoEmpresa) ? $infoEmpresa['razon_social'] : '') }}"
                                               placeholder="Nombre completo de la empresa" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="rfc" class="form-label">
                                            RFC *
                                        </label>
                                        <input type="text" class="form-control modern-input" id="rfc" name="rfc" 
                                               value="{{ old('rfc', isset($infoEmpresa) ? $infoEmpresa['rfc'] : '') }}"
                                               placeholder="RFC de la empresa" maxlength="13" required>
                                    </div>
                                </div>

                                <!-- Información de contacto -->
                                <div class="mt-4">
                                    <h6 class="section-subtitle">
                                        <i class="bi bi-telephone me-2"></i>Información de Contacto
                                    </h6>
                                    <div class="mb-3">
                                        <label for="telefono" class="form-label">
                                            Número de Teléfono
                                        </label>
                                        <input type="tel" class="form-control modern-input" id="telefono" name="telefono" 
                                               value="{{ old('telefono', isset($infoEmpresa) ? $infoEmpresa['telefono'] : '') }}"
                                               placeholder="(55) 1234-5678">
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">
                                            Email Corporativo
                                        </label>
                                        <input type="email" class="form-control modern-input" id="email" name="email" 
                                               value="{{ old('email', isset($infoEmpresa) ? $infoEmpresa['email'] : '') }}"
                                               placeholder="contacto@empresa.com">
                                    </div>
                                </div>
                            </div>

                            <!-- Dirección fiscal (columna derecha) -->
                            <div class="col-md-6">
                                <div>
                                    <h6 class="section-subtitle">
                                        <i class="bi bi-geo-alt me-2"></i>Dirección Fiscal
                                    </h6>
                                    <!-- Fila 1: Calle y número -->
                                    <div class="mb-3">
                                        <label for="calle_numero" class="form-label">
                                            Calle y Número *
                                        </label>
                                        <input type="text" class="form-control modern-input" id="calle_numero" name="calle_numero" 
                                               value="{{ old('calle_numero', isset($infoEmpresa) ? $infoEmpresa['calle_numero'] : '') }}"
                                               placeholder="Ej: Av. Reforma 123" required>
                                    </div>
                                    <!-- Fila 2: Colonia y Código Postal -->
                                    <div class="row mb-3">
                                        <div class="col-md-7">
                                            <label for="colonia" class="form-label">
                                                Colonia *
                                            </label>
                                            <input type="text" class="form-control modern-input" id="colonia" name="colonia" 
                                                   value="{{ old('colonia', isset($infoEmpresa) ? $infoEmpresa['colonia'] : '') }}"
                                                   placeholder="Nombre de la colonia" required>
                                        </div>
                                        <div class="col-md-5">
                                            <label for="codigo_postal" class="form-label">
                                                Código Postal *
                                            </label>
                                            <input type="text" class="form-control modern-input" id="codigo_postal" name="codigo_postal" 
                                                   value="{{ old('codigo_postal', isset($infoEmpresa) ? $infoEmpresa['codigo_postal'] : '') }}"
                                                   placeholder="00000" maxlength="5" required>
                                        </div>
                                    </div>
                                    <!-- Fila 3: Ciudad -->
                                    <div class="mb-3">
                                        <label for="ciudad" class="form-label">
                                            Ciudad *
                                        </label>
                                        <input type="text" class="form-control modern-input" id="ciudad" name="ciudad" 
                                               value="{{ old('ciudad', isset($infoEmpresa) ? $infoEmpresa['ciudad'] : '') }}"
                                               placeholder="Ciudad" required>
                                    </div>
                                    <!-- Fila 4: Estado y País -->
                                    <div class="row mb-3">
                                        <div class="col-md-7">
                                            <label for="estado" class="form-label">
                                                Estado *
                                            </label>
                                            <select class="form-select modern-input" id="estado" name="estado" required>
                                                <option value="">Seleccionar estado</option>
                                                @php
                                                    $estados = [
                                                        'Aguascalientes', 'Baja California', 'Baja California Sur', 'Campeche', 
                                                        'Chiapas', 'Chihuahua', 'Ciudad de México', 'Coahuila', 'Colima', 
                                                        'Durango', 'Estado de México', 'Guanajuato', 'Guerrero', 'Hidalgo', 
                                                        'Jalisco', 'Michoacán', 'Morelos', 'Nayarit', 'Nuevo León', 'Oaxaca', 
                                                        'Puebla', 'Querétaro', 'Quintana Roo', 'San Luis Potosí', 'Sinaloa', 
                                                        'Sonora', 'Tabasco', 'Tamaulipas', 'Tlaxcala', 'Veracruz', 'Yucatán', 'Zacatecas'
                                                    ];
                                                    $estadoSeleccionado = old('estado', isset($infoEmpresa) ? $infoEmpresa['estado'] : '');
                                                @endphp
                                                @foreach($estados as $estado)
                                                    <option value="{{ $estado }}" {{ $estadoSeleccionado == $estado ? 'selected' : '' }}>
                                                        {{ $estado }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-5">
                                            <label for="pais" class="form-label">
                                                País *
                                            </label>
                                            <input type="text" class="form-control modern-input" id="pais" name="pais" 
                                                   value="{{ old('pais', isset($infoEmpresa) ? ($infoEmpresa['pais'] ?: 'México') : 'México') }}" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="card-actions mt-4">
                            <div class="d-flex justify-content-end gap-3">
                                <button type="button" class="btn btn-outline-secondary" onclick="resetEmpresaForm()">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Limpiar
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>Guardar Información
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        

        <!-- Tarjeta de Mensaje de Recordatorio WhatsApp -->
        <div class="whatsapp-reminder-section mb-5 w-100" style="margin-right: 0; margin-left: 0; max-width: 100%;">
            <div class="card modern-card">
                <div class="card-header" style="background: #fff;">
                    <div class="header-content">
                        <div class="header-icon" style="background: linear-gradient(90deg, #25D366 0%, #128C7E 100%); color:#fff;">
                            <i class="bi bi-whatsapp"></i>
                        </div>
                        <div class="header-text">
                            <h4 class="card-title mb-1">Mensaje de Recordatorio WhatsApp</h4>
                            <p class="card-subtitle text-muted mb-0">Personaliza el mensaje que se envía a los clientes para recordar sus pagos</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form id="whatsapp-form" action="{{ route('ajustes.recordatorioWhatsApp') }}" method="POST">
                        @csrf
                        
                        <!-- Información sobre variables disponibles -->
                        <div class="alert alert-info mb-4">
                            <div class="w-100 mb-3">
                                <h6 class="alert-heading mb-2">
                                    <i class="bi bi-info-circle-fill me-1 mt-1"></i>    
                                    Variables disponibles:
                                </h6>
                                <div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <ul class="list-unstyled mb-0">
                                                <li><code>{nombreCliente}</code> - Nombre del cliente</li>
                                                <li><code>{nombrePaquete}</code> - Nombre del paquete contratado</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <ul class="list-unstyled mb-0">
                                                <li><code>{cantidadPagoProximo}</code> - Monto del próximo pago</li>
                                                <li><code>{fechaPago}</code> - Fecha del próximo pago</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mensaje_recordatorio" class="form-label">
                                        <i class="bi bi-chat-text me-2"></i>Mensaje de Recordatorio
                                    </label>
                                    <textarea class="form-control modern-input" id="mensaje_recordatorio" name="mensaje_recordatorio" 
                                              rows="6" placeholder="Escribe aquí el mensaje que se enviará por WhatsApp..." 
                                              maxlength="250" required>{{ old('mensaje_recordatorio', $mensajeRecordatorio ?? 'Hola {nombreCliente}, te recordamos que el pago de tu paquete {nombrePaquete} por {cantidadPagoProximo} será cobrado el día {fechaPago}.') }}
                                    </textarea>
                                    <div class="form-text d-flex justify-content-between">
                                        <span>Usa las variables disponibles para personalizar el mensaje</span>
                                        <span id="contadorCaracteres">0/250 caracteres</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <!-- Preview del mensaje -->
                                 <div class="mb-2">
                                    <label for="mensaje_recordatorio" class="form-label">
                                        <i class="bi bi-eye me-2"></i>Vista previa del mensaje
                                    </label>
                                </div>
                                <div class="card bg-light h-100 ">
                                    <div class="card-body d-flex align-items-center ">
                                        <div class="whatsapp-preview p-3 rounded w-100" style="background: #DCF8C6; border-left: 4px solid #25D366;">
                                            <div class="d-flex align-items-start">
                                                <i class="bi bi-whatsapp text-success me-2 mt-1"></i>
                                                <div>
                                                    <small class="text-muted d-block mb-1">Vista previa con datos de ejemplo:</small>
                                                    <p id="previewMensaje" class="mb-0" style="font-size: 0.95rem; line-height: 1.4;">
                                                        Hola Juan Pérez, te recordamos que el pago de tu paquete Premium por $2,500.00 será cobrado el día 28 de agosto de 2025.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="card-actions mt-4 border-0">
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary" onclick="resetWhatsAppForm()">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Restaurar por Defecto
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-whatsapp me-2"></i>Guardar Mensaje
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tarjeta de Tolerancia de Pagos -->
        <div class="tolerancia-pagos-section mb-5 w-100" style="margin-right: 0; margin-left: 0; max-width: 100%;">
            <div class="card modern-card">
                <div class="card-header" style="background: #fff;">
                    <div class="header-content">
                        <div class="header-icon" style="background: linear-gradient(90deg, #ff6b35 0%, #f7931e 100%); color:#fff;">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="header-text">
                            <h4 class="card-title mb-1">Tolerancia de Pagos</h4>
                            <p class="card-subtitle text-muted mb-0">Define cuántos días de gracia se darán antes de considerar un pago como retrasado</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form id="tolerancia-form" action="{{ route('ajustes.toleranciaPagos') }}" method="POST">
                        @csrf
                 

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tolerancia_dias" class="form-label">
                                        <i class="bi bi-calendar-plus me-2"></i>Días de Tolerancia
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control modern-input" id="tolerancia_dias" name="tolerancia_dias" 
                                               value="{{ old('tolerancia_dias', $toleranciaPagos ?? 0) }}" 
                                               min="0" max="365" placeholder="0" required>
                                        <span class="input-group-text">días</span>
                                    </div>
                                    <div class="form-text">
                                        Introduce un número entre 0 y 365 días
                                    </div>
                                </div>

                                <!-- Ejemplos visuales -->
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-lightbulb me-2"></i>Valores recomendados
                                    </label>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="setTolerancia(0)">
                                            0 días (estricto)
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="setTolerancia(3)">
                                            3 días (moderado)
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="setTolerancia(7)">
                                            7 días (flexible)
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="setTolerancia(15)">
                                            15 días (muy flexible)
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <!-- Vista previa del efecto -->
                                <div class="mb-2">
                                    <label class="form-label">
                                        <i class="bi bi-eye me-2"></i>Efecto actual
                                    </label>
                                </div>
                                <div class="card bg-light h-100">
                                    <div class="card-body">
                                        <div class="tolerance-preview p-3 rounded w-100" style="background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%); border-left: 4px solid #ff6b35;">
                                            <div class="d-flex align-items-start">
                                                <i class="bi bi-calendar-check text-warning me-2 mt-1"></i>
                                                <div>
                                                    <small class="text-muted d-block mb-1">Con la configuración actual:</small>
                                                    <p id="previewTolerancia" class="mb-2" style="font-size: 0.95rem; line-height: 1.4;">
                                                        Los pagos se considerarán retrasados <strong><span id="diasActuales">{{ $toleranciaPagos ?? 0 }}</span> día(s)</strong> después de su fecha de vencimiento.
                                                    </p>
                                                    <div class="small text-muted">
                                                        <strong>Ejemplo:</strong> Un pago con vencimiento el 1 de septiembre se marcará como retrasado el <span id="fechaEjemplo">{{ now()->addDays($toleranciaPagos ?? 0)->format('d \d\e F') }}</span>.
                                                    </div>
                                                    @php
                                                        // Estadísticas rápidas del efecto actual
                                                        $contratosConVencidas = \App\Models\Contrato::whereHas('pagos', function($q) {
                                                            $tolerancia = \App\Models\Ajuste::obtenerToleranciaPagos();
                                                            $fechaLimite = \Carbon\Carbon::now()->subDays($tolerancia)->endOfDay();
                                                            $q->where('estado', 'pendiente')
                                                              ->where('fecha_pago', '<', $fechaLimite);
                                                        })->count();
                                                        
                                                        $contratosEnTolerancia = \App\Models\Contrato::whereHas('pagos', function($q) {
                                                            $tolerancia = \App\Models\Ajuste::obtenerToleranciaPagos();
                                                            if ($tolerancia > 0) {
                                                                $fechaLimiteTolerancia = \Carbon\Carbon::now()->subDays($tolerancia)->endOfDay();
                                                                $q->where('estado', 'pendiente')
                                                                  ->where('fecha_pago', '<', \Carbon\Carbon::now()->endOfDay())
                                                                  ->where('fecha_pago', '>=', $fechaLimiteTolerancia);
                                                            }
                                                        })->count();
                                                    @endphp
                                                    <div class="mt-2 pt-2 border-top">
                                                        <div class="row text-center">
                                                            <div class="col-6">
                                                                <div class="small fw-bold text-danger">{{ $contratosConVencidas }}</div>
                                                                <div class="small text-muted">Retrasados</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="small fw-bold text-warning">{{ $contratosEnTolerancia }}</div>
                                                                <div class="small text-muted">En gracia</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="card-actions mt-4 border-0">
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary" onclick="resetToleranciaForm()">
                                    <i class="bi bi-arrow-clockwise me-2"></i>Restablecer a 0
                                </button>
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-save me-2"></i>Guardar Tolerancia
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @auth
        <div class="w-100 mb-5">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-danger btn-lg w-100" style="border-radius: 14px; font-weight: 700;">
                    <i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión
                </button>
            </form>
        </div>
        @endauth

    </div>
</div>

<!-- Scripts de Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Estilos modernos para navegación -->
<style>
    .navigation-cards {
        margin-top: 2rem;
    }

    .nav-card {
        display: flex;
        align-items: center;
        padding: 1.5rem;
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(0, 0, 0, 0.04);
        position: relative;
        overflow: hidden;
        height: 100%;
    }

    .nav-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #E1B240 0%, #79481D 100%);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .nav-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 28px rgba(225, 178, 64, 0.3);
        border-color: #E1B240;
    }

    .nav-card:hover::before {
        transform: scaleX(1);
    }

    .nav-card-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        font-size: 1.5rem;
        color: white;
        background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(225, 178, 64, 0.4);
    }

    .nav-card-content {
        flex: 1;
        min-width: 0;
    }

    .nav-card-title {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
        color: #1a1a1a;
        line-height: 1.3;
    }

    .nav-card-subtitle {
        margin: 0.25rem 0 0 0;
        font-size: 0.875rem;
        color: #6b7280;
        line-height: 1.3;
    }

    .nav-card-arrow {
        color: #9ca3af;
        font-size: 1.25rem;
        transition: all 0.3s ease;
        margin-left: 1rem;
    }

    .nav-card:hover .nav-card-arrow {
        color: #E1B240;
        transform: translateX(4px);
    }

    /* Efectos adicionales */
    .nav-card:active {
        transform: translateY(-2px);
    }

    /* Responsivo */
    @media (max-width: 991px) {
        .nav-card {
            margin-bottom: 1rem;
        }
    }

    @media (max-width: 768px) {
        .nav-card {
            padding: 1.25rem;
        }
        
        .nav-card-icon {
            width: 42px;
            height: 42px;
            font-size: 1.25rem;
        }
        
        .nav-card-title {
            font-size: 1rem;
        }
        
        .nav-card-subtitle {
            font-size: 0.8rem;
        }
    }

    @media (max-width: 576px) {
        .navigation-cards .row {
            flex-direction: column;
        }
        
        .navigation-cards .col-lg-3 {
            width: 100%;
            max-width: 100%;
        }
    }

    /* Estilos para la tarjeta de información del usuario */
    .user-info-section {
        max-width: 1200px;
        margin: 0 auto;
    }

    .user-info-block {
        height: 100%;
    }

    .info-item {
        padding: 0.75rem 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: #374151;
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
        display: block;
    }

    .info-value {
        color: #1f2937;
        font-size: 0.95rem;
        padding: 0.25rem 0;
        word-break: break-word;
    }

    .user-actions-info {
        flex: 1;
    }

    .user-actions-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
        border-radius: 8px;
        font-weight: 600;
    }

    .badge.bg-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        color: white;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
    }

    .badge.bg-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
        color: white;
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
    }

    /* Estilos para badges de roles */
    .role-badge {
        font-size: 0.8rem;
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        border: 2px solid transparent;
        transition: all 0.3s ease;
    }

    .role-badge.role-admin {
        background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
        color: white;
        border-color: #dc2626;
    }

    .role-badge.role-gerente {
        background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
        color: white;
        border-color: #7c3aed;
    }

    .role-badge.role-supervisor {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: white;
        border-color: #2563eb;
    }

    .role-badge.role-vendedor {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        color: white;
        border-color: #059669;
    }

    .role-badge.role-default {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        color: white;
        border-color: #6b7280;
    }

    .role-badge:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .btn-outline-primary {
        border: 2px solid #3B82F6;
        color: #3B82F6;
        border-radius: 12px;
        padding: 0.5rem 1rem;
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        background: white;
    }

    .btn-outline-primary:hover {
        background: #3B82F6;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    .btn-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        border: none;
        border-radius: 12px;
        padding: 0.5rem 1rem;
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        color: white;
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
    }

    .btn-warning:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        color: white;
    }

    /* Responsivo para la tarjeta de usuario */
    @media (max-width: 768px) {
        .user-actions-buttons {
            width: 100%;
            justify-content: stretch;
        }

        .user-actions-buttons .btn {
            flex: 1;
            min-width: 120px;
        }

        .user-actions-info {
            width: 100%;
            margin-bottom: 1rem;
        }

        .card-actions .d-flex {
            flex-direction: column;
            align-items: stretch !important;
        }
    }

    /* Estilos para la tarjeta de información de empresa */
    .enterprise-info-section {
        max-width: 1200px;
        margin: 0 auto;
    }

    .modern-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(0, 0, 0, 0.05);
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .modern-card:hover {
        box-shadow: 0 8px 32px rgba(225, 178, 64, 0.15);
    }

    .modern-card .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 2px solid #E1B240;
        padding: 1.5rem 2rem;
        border-radius: 16px 16px 0 0 !important;
    }

    .modern-card .card-body {
        padding: 2rem;
    }

    .modern-card .card-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0;
    }

    .modern-card .card-subtitle {
        font-size: 0.95rem;
        color: #6b7280;
        margin: 0;
    }

    .section-subtitle {
        font-size: 1.1rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #f3f4f6;
        display: flex;
        align-items: center;
    }

    .section-subtitle i {
        color: #E1B240;
        font-size: 1rem;
    }

    .modern-input {
        border-radius: 12px;
        border: 2px solid #e5e7eb;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .modern-input:focus {
        border-color: #E1B240;
        box-shadow: 0 0 0 3px rgba(225, 178, 64, 0.1);
        outline: none;
    }

    .modern-input:hover {
        border-color: #d1d5db;
    }

    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        font-size: 0.9rem;
    }

    .form-label i {
        color: #6b7280;
        font-size: 0.85rem;
    }

    .card-actions {
        border-top: 1px solid #f3f4f6;
        padding-top: 1.5rem;
        margin-top: 2rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
        border: none;
        border-radius: 12px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(225, 178, 64, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(225, 178, 64, 0.4);
        background: linear-gradient(135deg, #c9a038 0%, #6a3e18 100%);
    }

    .btn-primary:active {
        transform: translateY(0);
    }

    .btn-outline-secondary {
        border: 2px solid #e5e7eb;
        color: #6b7280;
        border-radius: 12px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background: white;
    }

    .btn-outline-secondary:hover {
        border-color: #d1d5db;
        background: #f9fafb;
        color: #374151;
        transform: translateY(-1px);
    }

    /* Responsivo para la tarjeta de empresa */
    @media (max-width: 768px) {
        .modern-card .card-header {
            padding: 1.25rem 1.5rem;
        }

        .modern-card .card-body {
            padding: 1.5rem;
        }

        .modern-card .card-title {
            font-size: 1.25rem;
        }

        .card-actions {
            margin-top: 1.5rem;
        }

        .card-actions .d-flex {
            flex-direction: column;
            gap: 0.75rem !important;
        }

        .card-actions .btn {
            width: 100%;
        }
    }

    /* Validación visual */
    .modern-input:invalid {
        border-color: #ef4444;
    }

    .modern-input:valid {
        border-color: #10b981;
    }

    /* Loading states */
    .btn.loading {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .btn.loading i {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* Estilos para la tarjeta de WhatsApp */
    .whatsapp-reminder-section {
        max-width: 1200px;
        margin: 0 auto;
    }

    .whatsapp-preview {
        transition: all 0.3s ease;
    }

    .whatsapp-preview:hover {
        box-shadow: 0 2px 8px rgba(37, 211, 102, 0.2);
    }

    /* Estilos para el botón de WhatsApp */
    .btn-success {
        background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
        border: none;
        border-radius: 12px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
    }

    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(37, 211, 102, 0.4);
        background: linear-gradient(135deg, #20bb5a 0%, #0f7669 100%);
    }

    /* Estilos para el contador de caracteres */
    #contadorCaracteres {
        font-weight: 600;
        transition: color 0.3s ease;
    }

    #contadorCaracteres.warning {
        color: #f59e0b;
    }

    #contadorCaracteres.danger {
        color: #ef4444;
    }

    /* Estilos para el código en las variables */
    code {
        background: #f3f4f6;
        padding: 0.2rem 0.4rem;
        border-radius: 4px;
        font-size: 0.85rem;
        color: #374151;
        font-weight: 600;
    }

    /* Estilos para la alerta de información */
    .alert-info {
        background: linear-gradient(135deg, #e0f2fe 0%, #b3e5fc 100%);
        border: 1px solid #81d4fa;
        border-radius: 12px;
    }

    .alert-info .alert-heading {
        color: #0277bd;
        font-weight: 700;
    }

    .alert-info code {
        background: #ffffff;
        color: #0277bd;
        border: 1px solid #81d4fa;
    }
</style>

<!-- Script personalizado -->
<script>
    // Función para resetear el formulario
    function resetForm() {
        if(confirm('¿Estás seguro de que deseas restaurar todos los valores por defecto?')) {
            document.getElementById('settings-form').reset();
            // Restaurar valores específicos
            document.getElementById('nombre_sistema').value = 'Shalom ERP';
            document.getElementById('timezone').value = 'America/Mexico_City';
            document.getElementById('idioma').value = 'Español';
            document.getElementById('moneda').value = 'MXN - Peso Mexicano';
            document.getElementById('rol_default').value = 'Usuario';
            document.getElementById('session_timeout').value = '120';
            document.getElementById('password_policy').value = 'Intermedia (8 caracteres, mayúscula, número)';
            
            // Activar switches por defecto
            document.getElementById('registro_usuarios').checked = true;
            document.getElementById('notif_email').checked = true;
            document.getElementById('notif_browser').checked = true;
            document.getElementById('two_factor').checked = true;
            
            // Desactivar switches por defecto
            document.getElementById('verificacion_email').checked = false;
            document.getElementById('notif_pagos').checked = false;
            document.getElementById('notif_contratos').checked = false;
            
            alert('Valores restaurados exitosamente!');
        }
    }

    // Manejar envío del formulario
    const settingsForm = document.getElementById('settings-form');
    if (settingsForm) {
        settingsForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if(confirm('¿Estás seguro de que deseas guardar todos los cambios de configuración?')) {
                // Aquí iría la lógica para enviar el formulario via AJAX
                // Por ahora solo mostramos una confirmación
                
                // Simular loading
                const submitBtn = document.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="bi bi-clock me-2"></i>Guardando...';
                submitBtn.disabled = true;
                
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    alert('¡Configuración guardada exitosamente!');
                }, 2000);
            }
        });
    }

    // Animación suave al hacer scroll a secciones
    document.addEventListener('DOMContentLoaded', function() {
        // Añadir animación de entrada a las cards
        const cards = document.querySelectorAll('.card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'all 0.3s ease';
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });

    // Validación en tiempo real para campos importantes
    const sessionTimeoutField = document.getElementById('session_timeout');
    if (sessionTimeoutField) {
        sessionTimeoutField.addEventListener('input', function() {
            const value = parseInt(this.value);
            if (value < 30) {
                this.setCustomValidity('El tiempo mínimo de sesión es 30 minutos');
            } else if (value > 480) {
                this.setCustomValidity('El tiempo máximo de sesión es 480 minutos (8 horas)');
            } else {
                this.setCustomValidity('');
            }
        });
    }

    // Confirmar cambios importantes en seguridad
    const securityInputs = ['two_factor', 'session_timeout', 'password_policy'];
    securityInputs.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('change', function() {
                if (this.type === 'checkbox' && !this.checked && this.id === 'two_factor') {
                    if (!confirm('¿Estás seguro de desactivar la autenticación de dos factores? Esto reducirá la seguridad del sistema.')) {
                        this.checked = true;
                    }
                }
            });
        }
    });
    
    // Funciones para la tarjeta de información del usuario
    function refreshUserInfo() {
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        
        // Estado de carga
        btn.innerHTML = '<i class="bi bi-arrow-clockwise me-2" style="animation: spin 1s linear infinite;"></i>Actualizando...';
        btn.disabled = true;
        
        // Simular actualización (en un caso real, haría una petición AJAX)
        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            
            // Mostrar mensaje de éxito
            showSuccessMessage('Información del usuario actualizada correctamente');
            
            // Recargar la página para mostrar datos actualizados
            window.location.reload();
        }, 1500);
    }

    function resendVerification() {
        if(!confirm('¿Deseas reenviar el correo de verificación a tu dirección de email?')) {
            return;
        }
        
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        
        // Estado de carga
        btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Enviando...';
        btn.disabled = true;
        
        // Simular envío de verificación
        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            
            // Mostrar mensaje de éxito
            showSuccessMessage('Correo de verificación enviado correctamente. Revisa tu bandeja de entrada.');
        }, 2000);
    }

    // Funciones para el formulario de empresa
    function resetEmpresaForm() {
        if(confirm('¿Estás seguro de que deseas limpiar todos los campos del formulario?')) {
            document.getElementById('empresa-form').reset();
        }
    }

    // Validación en tiempo real para el RFC
    const rfcField = document.getElementById('rfc');
    if (rfcField) {
        rfcField.addEventListener('input', function() {
            let rfc = this.value.toUpperCase();
            this.value = rfc;
            
            // Validación básica del RFC
            const rfcPattern = /^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/;
            if (rfc.length > 0 && !rfcPattern.test(rfc) && rfc.length === 13) {
                this.setCustomValidity('El RFC no tiene un formato válido');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
            }
        });
    }

    // Validación para código postal
    const codigoPostalField = document.getElementById('codigo_postal');
    if (codigoPostalField) {
        codigoPostalField.addEventListener('input', function() {
            const cp = this.value;
            const cpPattern = /^[0-9]{5}$/;
            
            if (cp.length > 0 && !cpPattern.test(cp)) {
                this.setCustomValidity('El código postal debe tener 5 dígitos');
            } else {
                this.setCustomValidity('');
            }
        });
    }

    // Formateo automático del teléfono
    const telefonoField = document.getElementById('telefono');
    if (telefonoField) {
        telefonoField.addEventListener('input', function() {
            let phone = this.value.replace(/\D/g, '');
            
            if (phone.length >= 10) {
                phone = phone.substring(0, 10);
                // Formato: (55) 1234-5678
                phone = `(${phone.substring(0, 2)}) ${phone.substring(2, 6)}-${phone.substring(6, 10)}`;
            }
            
            this.value = phone;
        });
    }

    // Manejar envío del formulario de empresa
    const empresaForm = document.getElementById('empresa-form');
    if (empresaForm) {
        empresaForm.addEventListener('submit', function(e) {
            if(!confirm('¿Estás seguro de guardar la información de la empresa? Esta información aparecerá en todos los recibos.')) {
                e.preventDefault();
                return false;
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalHtml = submitBtn.innerHTML;
        
            // Estado de carga
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Guardando...';
            submitBtn.disabled = true;
            submitBtn.classList.add('loading');
            
            // El formulario se enviará normalmente al servidor
        });
    }

    // Función para mostrar mensajes de éxito
    function showSuccessMessage(message) {
        // Crear alerta temporal
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show mb-4';
        alertDiv.innerHTML = `
            <i class="bi bi-check-circle-fill me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insertar después del header
        const pageHeader = document.querySelector('.page-header');
        pageHeader.parentNode.insertBefore(alertDiv, pageHeader.nextSibling);
        
        // Auto-remover después de 5 segundos
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }

    // Cargar datos existentes de la empresa (si existen)
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, inicializando funcionalidad WhatsApp...');
        
        // Animación de entrada para las tarjetas
        const userCard = document.querySelector('.user-info-section .modern-card');
        const empresaCard = document.querySelector('.enterprise-info-section .modern-card');
        const whatsappCard = document.querySelector('.whatsapp-reminder-section .modern-card');
        
        if (userCard) {
            userCard.style.opacity = '0';
            userCard.style.transform = 'translateY(30px)';
            userCard.style.transition = 'all 0.6s ease';
            
            setTimeout(() => {
                userCard.style.opacity = '1';
                userCard.style.transform = 'translateY(0)';
            }, 200);
        }
        
        if (empresaCard) {
            empresaCard.style.opacity = '0';
            empresaCard.style.transform = 'translateY(30px)';
            empresaCard.style.transition = 'all 0.6s ease';
            
            setTimeout(() => {
                empresaCard.style.opacity = '1';
                empresaCard.style.transform = 'translateY(0)';
            }, 400);
        }

        if (whatsappCard) {
            whatsappCard.style.opacity = '0';
            whatsappCard.style.transform = 'translateY(30px)';
            whatsappCard.style.transition = 'all 0.6s ease';
            
            setTimeout(() => {
                whatsappCard.style.opacity = '1';
                whatsappCard.style.transform = 'translateY(0)';
            }, 600);
        }

        // Inicializar funcionalidad de WhatsApp con más delay
        setTimeout(function() {
            initWhatsAppFunctionality();
        }, 800);
    });

    // Funciones para el mensaje de WhatsApp
    function initWhatsAppFunctionality() {
        console.log('🚀 Iniciando funcionalidad WhatsApp...');
        
        const textarea = document.getElementById('mensaje_recordatorio');
        const contador = document.getElementById('contadorCaracteres');
        const preview = document.getElementById('previewMensaje');

        console.log('📋 Elementos encontrados:', {
            textarea: !!textarea,
            textareaValue: textarea ? textarea.value : 'NO ENCONTRADO',
            contador: !!contador,
            contadorText: contador ? contador.textContent : 'NO ENCONTRADO',
            preview: !!preview
        });

        if (textarea && contador && preview) {
            console.log('✅ Todos los elementos encontrados');
            console.log('📝 Valor inicial del textarea:', `"${textarea.value}"`);
            console.log('📏 Longitud inicial:', textarea.value.length);
            console.log('🔢 Contador actual:', contador.textContent);
            
            // Definir las funciones primero
            function updateCharacterCount() {
                const length = textarea.value.length;
                const maxLength = 250;
                const texto = `${length}/${maxLength} caracteres`;
                
                console.log('🔄 Actualizando contador de', contador.textContent, 'a', texto);
                contador.textContent = texto;

                // Cambiar color según la cantidad
                contador.classList.remove('warning', 'danger');
                if (length > maxLength * 0.8) { // Más de 200 caracteres
                    contador.classList.add('warning');
                }
                if (length > maxLength * 0.95) { // Más de 237 caracteres
                    contador.classList.add('danger');
                }
                
                console.log('✅ Contador actualizado a:', contador.textContent);
            }

            function updatePreview() {
                let mensaje = textarea.value;
                
                // Reemplazar variables con datos de ejemplo
                const variables = {
                    '{nombreCliente}': 'Juan Pérez',
                    '{nombrePaquete}': 'Premium',
                    '{cantidadPagoProximo}': '$2,500.00',
                    '{fechaPago}': '28 de agosto de 2025'
                };

                for (const [variable, valor] of Object.entries(variables)) {
                    mensaje = mensaje.replace(new RegExp(variable.replace(/[{}]/g, '\\$&'), 'g'), valor);
                }

                preview.textContent = mensaje || 'Escribe un mensaje para ver la vista previa...';
            }

            // Forzar actualización inicial inmediata y repetida
            console.log('🔄 Forzando actualización inicial...');
            
            // Actualización inmediata
            updateCharacterCount();
            updatePreview();
            
            // Repetir después de un small delay para asegurar que el DOM esté listo
            setTimeout(() => {
                console.log('🔄 Segunda actualización después de delay...');
                updateCharacterCount();
                updatePreview();
            }, 100);
            
            // Y una más por si acaso
            setTimeout(() => {
                console.log('🔄 Tercera actualización final...');
                updateCharacterCount();
                updatePreview();
            }, 500);
            
            // Agregar múltiples event listeners para asegurar que funcione
            ['input', 'keyup', 'change', 'propertychange'].forEach(function(eventType) {
                textarea.addEventListener(eventType, function() {
                    console.log('Evento:', eventType, 'Longitud:', textarea.value.length);
                    updateCharacterCount();
                    updatePreview();
                });
            });

            textarea.addEventListener('paste', function() {
                setTimeout(() => {
                    updateCharacterCount();
                    updatePreview();
                }, 50);
            });
            
            console.log('Funcionalidad WhatsApp inicializada correctamente');
        } else {
            console.error('No se pudieron encontrar todos los elementos necesarios');
        }
    }

    function resetWhatsAppForm() {
        if(confirm('¿Estás seguro de que deseas restaurar el mensaje por defecto?')) {
            const defaultMessage = 'Hola {nombreCliente}, te recordamos que el pago de tu paquete {nombrePaquete} por {cantidadPagoProximo} será cobrado el día {fechaPago}.';
            const textarea = document.getElementById('mensaje_recordatorio');
            const contador = document.getElementById('contadorCaracteres');
            const preview = document.getElementById('previewMensaje');
            
            textarea.value = defaultMessage;
            
            // Actualizar contador y preview inmediatamente
            if (contador && preview) {
                const length = textarea.value.length;
                contador.textContent = `${length}/250 caracteres`;
                
                // Actualizar preview
                let mensaje = defaultMessage;
                const variables = {
                    '{nombreCliente}': 'Juan Pérez',
                    '{nombrePaquete}': 'Premium',
                    '{cantidadPagoProximo}': '$2,500.00',
                    '{fechaPago}': '28 de agosto de 2025'
                };

                for (const [variable, valor] of Object.entries(variables)) {
                    mensaje = mensaje.replace(new RegExp(variable.replace(/[{}]/g, '\\$&'), 'g'), valor);
                }
                preview.textContent = mensaje;
            }
        }
    }

    // Manejar envío del formulario de WhatsApp
    const whatsappForm = document.getElementById('whatsapp-form');
    if (whatsappForm) {
        whatsappForm.addEventListener('submit', function(e) {
            if(!confirm('¿Estás seguro de guardar este mensaje de recordatorio? Se utilizará para todos los recordatorios de pago por WhatsApp.')) {
                e.preventDefault();
                return false;
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalHtml = submitBtn.innerHTML;
            
            // Estado de carga
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Guardando...';
            submitBtn.disabled = true;
            submitBtn.classList.add('loading');
            
            // El formulario se enviará normalmente al servidor
        });
    }

    // Funciones para tolerancia de pagos
    function setTolerancia(dias) {
        const input = document.getElementById('tolerancia_dias');
        if (input) {
            input.value = dias;
            updateToleranciaPreview();
        }
    }

    function resetToleranciaForm() {
        const input = document.getElementById('tolerancia_dias');
        if (input) {
            input.value = 0;
            updateToleranciaPreview();
        }
    }

    function updateToleranciaPreview() {
        const input = document.getElementById('tolerancia_dias');
        const diasActuales = document.getElementById('diasActuales');
        const fechaEjemplo = document.getElementById('fechaEjemplo');
        
        if (input && diasActuales && fechaEjemplo) {
            const dias = parseInt(input.value) || 0;
            diasActuales.textContent = dias;
            
            // Calcular fecha de ejemplo
            const fechaBase = new Date();
            fechaBase.setDate(fechaBase.getDate() + dias);
            
            const opciones = { 
                day: 'numeric', 
                month: 'long'
            };
            
            fechaEjemplo.textContent = fechaBase.toLocaleDateString('es-ES', opciones);
        }
    }

    // Event listener para el input de tolerancia
    const toleranciaInput = document.getElementById('tolerancia_dias');
    if (toleranciaInput) {
        toleranciaInput.addEventListener('input', updateToleranciaPreview);
        // Actualizar preview inicial
        updateToleranciaPreview();
    }

    // Manejar envío del formulario de tolerancia
    const toleranciaForm = document.getElementById('tolerancia-form');
    if (toleranciaForm) {
        toleranciaForm.addEventListener('submit', function(e) {
            const dias = parseInt(document.getElementById('tolerancia_dias').value) || 0;
            
            if(!confirm(`¿Estás seguro de establecer la tolerancia de pagos a ${dias} días? Esto afectará cómo se muestran los pagos retrasados en todo el sistema.`)) {
                e.preventDefault();
                return false;
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalHtml = submitBtn.innerHTML;
            
            // Estado de carga
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Guardando...';
            submitBtn.disabled = true;
            submitBtn.classList.add('loading');
            
            // El formulario se enviará normalmente al servidor
        });
    }
</script>
@endsection