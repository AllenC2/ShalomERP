@extends('layouts.app')

@section('template_title')
    {{ $empleado->nombre ?? __('Show') . ' ' . __('Empleado') }}
@endsection

@section('content')
    @if(!$empleado)
        <div class="container py-4">
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                El empleado solicitado no existe.
            </div>
            <a href="{{ route('empleados.index') }}" class="btn btn-primary">
                <i class="bi bi-arrow-left me-2"></i>Volver a empleados
            </a>
        </div>
    @else
    <section class="content container-fluid">
        <div class="container py-2">
            <!-- Mensajes de éxito/error -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Header del empleado -->
            <div class="contract-header">
                <a href="{{ route('empleados.index') }}" class="modern-link mb-3 d-inline-block">
                    <i class="bi bi-arrow-left me-1"></i>
                    {{ __('Regresar') }}
                </a>
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <!-- Header moderno -->
                        <div class="page-header">
                            <div class="header-content">
                                <div class="header-icon">
                                    <i class="bi bi-person-badge"></i>
                                </div>
                                <div class="header-text">
                                    <h1 class="page-title">{{ $empleado->nombre }} {{ $empleado->apellido }}</h1>
                                    <p class="page-subtitle">Información del empleado y comisiones</p>
                                </div>
                            </div>
                            <div class="header-actions">
                                <div class="text-md-end">
                                    <span class="badge status-badge {{ $empleado->estado === 'activo' ? 'bg-success' : 'bg-danger' }}">
                                        {{ strtoupper($empleado->estado ?? 'ACTIVO') }}
                                    </span>
                                    <p class="text-muted mb-0 mt-2">
                                        Empleado desde: {{ $empleado->created_at->translatedFormat('d \d\e F \d\e Y') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                @php
                    $totalComisiones = 0;
                    $comisionesPendientes = 0;
                    $comisionesPagadas = 0;

                    foreach ($comisiones as $comision) {
                        // Si es una comisión padre con parcialidades, calcular el restante
                        if ($comision->comision_padre_id == null && $comision->parcialidades->count() > 0) {
                            $totalParcialidades = $comision->parcialidades->sum('monto');
                            $montoRestante = $comision->monto - $totalParcialidades;
                            
                            $totalComisiones += $montoRestante;
                            if ($montoRestante > 0) {
                                if ($comision->estado === 'Pendiente') {
                                    $comisionesPendientes += $montoRestante;
                                } elseif ($comision->estado === 'Pagada') {
                                    $comisionesPagadas += $montoRestante;
                                }
                            }
                        } 
                        // Si es una parcialidad o comisión normal
                        elseif ($comision->tipo_comision === 'PARCIALIDAD' || $comision->comision_padre_id == null) {
                            if ($comision->tipo_comision !== 'PARCIALIDAD') {
                                $totalComisiones += $comision->monto;
                                if ($comision->estado === 'Pendiente') {
                                    $comisionesPendientes += $comision->monto;
                                } elseif ($comision->estado === 'Pagada') {
                                    $comisionesPagadas += $comision->monto;
                                }
                            }
                        }
                    }
                @endphp
                

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm hover-card mb-4" style="background: linear-gradient(135deg, #e8f5e8 0%, #f8f9fa 100%);">
                        <div class="card-body p-4">
                            <!-- Header del empleado -->
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="employee-avatar me-3">
                                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 1.5em; font-weight: bold;">
                                            {{ strtoupper(substr($empleado->nombre, 0, 1) . substr($empleado->apellido, 0, 1)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-1 text-dark">
                                            {{ $empleado->nombre }} {{ $empleado->apellido }}
                                        </h5>
                                        <div class="d-flex gap-2 align-items-center">
                                            <span class="badge bg-primary">ID: {{ $empleado->id }}</span>
                                            <span class="badge bg-light text-dark border">Empleado</span>
                                            <span class="badge {{ $empleado->estado === 'activo' ? 'bg-success' : 'bg-danger' }} text-white">
                                                {{ ucfirst($empleado->estado ?? 'Activo') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Información de contacto -->
                            <div class="row g-3 mb-3">
                                @if($empleado->user && $empleado->user->email)
                                <div class="col-12">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-envelope text-primary"></i>
                                        <a href="mailto:{{ $empleado->user->email }}" class="text-decoration-none text-muted small">{{ $empleado->user->email }}</a>
                                    </div>
                                </div>
                                @endif

                                @if($empleado->telefono)
                                <div class="col-12">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-telephone text-success"></i>
                                        <a href="tel:{{ $empleado->telefono }}" class="text-decoration-none text-muted small">{{ $empleado->telefono }}</a>
                                    </div>
                                </div>
                                @endif
                                
                                <div class="col-12">
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-geo-alt text-primary mt-1"></i>
                                        <span class="text-muted small">
                                            {{ $empleado->domicilio ?? 'Domicilio no disponible' }}
                                        </span>
                                    </div>
                                </div>

                                
                            </div>

                            <!-- Estadísticas del empleado -->
                            <div class="border-top pt-3">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="employee-stat">
                                            <i class="bi bi-calendar-plus text-info d-block fs-5 mb-1"></i>
                                            <small class="text-muted d-block">Empleado desde</small>
                                            <small class="fw-bold">
                                                {{ $empleado->created_at->translatedFormat('F Y') }}
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="employee-stat">
                                            <i class="bi bi-cash-coin text-warning d-block fs-5 mb-1"></i>
                                            <small class="text-muted d-block">Comisiones</small>
                                            <small class="fw-bold">{{ count($comisiones) }}</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="employee-stat">
                                            <i class="bi bi-check-circle text-success d-block fs-5 mb-1"></i>
                                            <small class="text-muted d-block">Pagadas</small>
                                            <small class="fw-bold">{{ $comisiones->where('estado', 'Pagada')->count() }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Indicador de tiempo como empleado -->
                            @php
                            $tiempoEmpleado = $empleado->created_at->diffForHumans(null, true);
                            @endphp
                            <div class="mt-3">
                                <div class="border border-muted rounded p-2 text-center">
                                    <small class="text-muted">
                                        <i class="bi bi-star-fill text-warning me-1"></i>
                                        @if($tiempoEmpleado)
                                        Empleado registrado hace {{ $tiempoEmpleado }}
                                        @else
                                        Empleado recién registrado
                                        @endif
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Barra de acciones rápidas -->
                    <div class="mb-3">
                        <div class="btn-group w-100" role="group" aria-label="Acciones rápidas">
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#reciboConsolidadoModal">
                                <i class="bi bi-file-earmark-text me-1"></i> Generar recibo
                            </button>
                            <button type="button" class="btn btn-outline-dark" id="btnActividadRutas"
                                data-url="{{ route('empleados.actividadRutas', $empleado->id) }}">
                                <i class="bi bi-signpost-split me-1"></i> Actividad de Rutas
                            </button>
                            <a href="{{ route('empleados.edit', $empleado->id) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-pencil-square me-1"></i> Editar
                            </a>
                            @if($empleado->estado === 'activo')
                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#darDeBajaModal">
                                    <i class="bi bi-person-x me-1"></i> Dar de baja
                                </button>
                            @else
                                <form action="{{ route('empleados.reactivar', $empleado->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('¿Estás seguro de que quieres reactivar a este empleado?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-success">
                                        <i class="bi bi-person-check me-1"></i> Reactivar
                                    </button>
                                </form>
                            @endif
                        </div>
                        <div class="text-center mt-2">
                            <a href="#" class="text-muted text-decoration-none small" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                <i class="bi bi-key me-1"></i>Cambiar contraseña
                            </a>
                        </div>
                    </div>

                    <!-- Gestión de rol de usuario -->
                    @if($empleado->user)
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-3">
                                <i class="bi bi-shield-lock me-2"></i>Gestión de Permisos
                            </h6>
                            
                            <!-- Leyenda de estado actual -->
                            <div class="alert {{ $empleado->user->role === 'admin' ? 'alert-info' : 'alert-light' }} mb-3 py-2">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <small>
                                        @if($empleado->user->role === 'admin')
                                            <strong>Este empleado tiene permisos de Administrador</strong>
                                            <br>Puede acceder a todas las funciones del sistema.
                                        @else
                                            <strong>Este empleado tiene permisos de Empleado</strong>
                                            <br>Acceso limitado a funciones básicas del sistema.
                                        @endif
                                    </small>
                                </div>
                            </div>

                            <!-- Switch para cambiar rol -->
                            <form action="{{ route('empleados.toggleRol', $empleado->id) }}" method="POST" id="toggleRolForm">
                                @csrf
                                @method('PATCH')
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <label class="form-label mb-0 fw-semibold">Rol de usuario</label>
                                        <small class="d-block text-muted">Cambiar entre Empleado y Administrador</small>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input 
                                            class="form-check-input" 
                                            type="checkbox" 
                                            role="switch" 
                                            id="rolSwitch" 
                                            {{ $empleado->user->role === 'admin' ? 'checked' : '' }}
                                            onchange="confirmarCambioRol()"
                                            style="width: 3rem; height: 1.5rem; cursor: pointer;">
                                        <label class="form-check-label ms-2 fw-bold" for="rolSwitch" id="rolLabel">
                                            {{ $empleado->user->role === 'admin' ? 'ADMIN' : 'EMPLEADO' }}
                                        </label>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif
                    
                </div>

                <div class="col-md-8">
                    <!-- Historial de Comisiones -->
                    <div class="card">
                        <div class="card-header text-white border-0 rounded-top"
                            style="background-image: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <h6 class="mb-0">
                                    <i class="bi bi-cash-coin me-2"></i>Historial de Comisiones Detallado
                                </h6>
                            </div>
                        </div>
                        <div>
                            <div class="card-body">
                                <!-- Tabla de comisiones -->
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Contrato</th>
                                                <th>Fecha</th>
                                                <th>Tipo</th>
                                                <th>Monto</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($comisiones as $comision)
                                                <tr class="clickable-row" style="cursor:pointer;" onclick="window.location='{{ route('comisiones.show', $comision->id) }}'">
                                                    <td class="fw-semibold">{{ $comision->id }}</td>
                                                    <td>
                                                        @if($comision->contrato)
                                                            <div class="d-flex flex-column">
                                                                <span class="fw-semibold text-primary">
                                                                    {{ $comision->contrato->paquete->nombre ?? 'N/A' }} #{{ $comision->contrato->id }}
                                                                </span>
                                                                <small class="text-muted">
                                                                    {{ $comision->contrato->cliente->nombre ?? 'N/A' }} {{ $comision->contrato->cliente->apellido ?? '' }}
                                                                </small>
                                                            </div>
                                                        @else
                                                            <span class="text-muted">Sin contrato</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            {{ \Carbon\Carbon::parse($comision->fecha_comision)->translatedFormat('d \\d\\e F \\d\\e Y') }}
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <span class="badge {{ $comision->tipo_comision == 'PARCIALIDAD' ? 'bg-light text-dark border' : 'bg-info text-white' }}">
                                                            {{ strtoupper($comision->tipo_comision) }}
                                                        </span>
                                                        @if($comision->comision_padre_id && $comision->comisionPadre)
                                                            <br>
                                                            <small class="text-muted">
                                                                de {{ strtoupper($comision->comisionPadre->tipo_comision) }}
                                                            </small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($comision->comision_padre_id == null && $comision->parcialidades->count() > 0)
                                                            @php
                                                                $totalParcialidades = $comision->parcialidades->sum('monto');
                                                                $restante = $comision->monto - $totalParcialidades;
                                                            @endphp
                                                            <span class="fw-bold text-success">${{ number_format($restante, 2) }}</span>
                                                            <small class="d-block text-muted">
                                                                de ${{ number_format($comision->monto, 2) }} 
                                                            </small>
                                                        @else
                                                            <span class="fw-bold {{ $comision->tipo_comision == 'PARCIALIDAD' ? 'text-muted' : 'text-success' }}">
                                                                ${{ number_format($comision->monto, 2) }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($comision->estado === 'Pagada')
                                                            <span class="badge bg-success">Pagada</span>
                                                        @elseif($comision->estado === 'Pendiente')
                                                            <span class="badge bg-warning">Pendiente</span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ $comision->estado }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted py-4">
                                                        <i class="bi bi-cash-coin fs-2 d-block mb-2 text-muted"></i>
                                                        No hay comisiones registradas para este empleado.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
            </div>
        </div>
    </section>

    @php
        $plantillasAsignadas = $plantillasAsignadas ?? collect();
        $tieneRutasAsignadas = $plantillasAsignadas->isNotEmpty();
    @endphp

    <!-- Modal Dar de baja -->
    <div class="modal fade" id="darDeBajaModal" tabindex="-1" aria-labelledby="darDeBajaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="darDeBajaModalLabel">
                        <i class="bi bi-person-x text-danger me-2"></i>Dar de baja
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body pt-3">
                    @if($tieneRutasAsignadas)
                        <div class="alert alert-warning border-0 mb-3">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Este empleado tiene <strong>{{ $plantillasAsignadas->count() }}</strong>
                            ruta{{ $plantillasAsignadas->count() === 1 ? '' : 's' }} asignada{{ $plantillasAsignadas->count() === 1 ? '' : 's' }}.
                            Debe reasignarlas a otro empleado antes de darlo de baja.
                        </div>
                        <p class="small text-muted mb-2">Rutas asignadas:</p>
                        <ul class="list-group list-group-flush mb-0 rounded-3 border">
                            @foreach($plantillasAsignadas->take(8) as $plantilla)
                                <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-2">
                                    <span class="small fw-semibold">{{ $plantilla->nombre ?: ('Ruta #' . $plantilla->id) }}</span>
                                    <span class="text-muted small">{{ $plantilla->etiquetaFrecuencia() }}</span>
                                </li>
                            @endforeach
                            @if($plantillasAsignadas->count() > 8)
                                <li class="list-group-item px-3 py-2 text-muted small">
                                    y {{ $plantillasAsignadas->count() - 8 }} más…
                                </li>
                            @endif
                        </ul>
                    @else
                        <p class="mb-0">
                            ¿Seguro que quieres dar de baja a
                            <strong>{{ $empleado->nombre }} {{ $empleado->apellido }}</strong>?
                            Perderá el acceso al sistema.
                        </p>
                    @endif
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    @if($tieneRutasAsignadas)
                        <a href="{{ route('rutas.index', ['empleado_id' => $empleado->id]) }}" class="btn btn-outline-primary">
                            <i class="bi bi-signpost-split me-1"></i> Ir a rutas
                        </a>
                        <button type="button" class="btn btn-danger" disabled title="Reasigna las rutas primero">
                            <i class="bi bi-person-x me-1"></i> Dar de baja
                        </button>
                    @else
                        <form action="{{ route('empleados.darDeBaja', $empleado->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-person-x me-1"></i> Confirmar baja
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Cambio de Contraseña -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title fw-bold" id="changePasswordModalLabel">
                        <i class="bi bi-key-fill me-2"></i>Cambiar Contraseña
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('empleados.cambiarContrasena', $empleado->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <div class="alert alert-info border-0 shadow-sm mb-3">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            Esta acción cambiará la contraseña inmediatamente sin requerir la anterior.
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label fw-bold">Nueva Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required minlength="8">
                            </div>
                            <div class="form-text">Mínimo 8 caracteres.</div>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label fw-bold">Confirmar Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required minlength="8">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning fw-bold">
                            <i class="bi bi-save me-1"></i>Guardar Cambio
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .clickable-row {
            transition: all 0.2s ease;
        }
        
        .clickable-row:hover {
            background-color: #f8f9fa !important;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .table-hover tbody tr:hover td {
            background-color: #f8f9fa;
        }
        
        .clickable-row:active {
            transform: translateY(0);
        }

        /* Estilos para los botones de acción */
        .btn-group form,
        .btn-group > .btn {
            flex: 1 1 0;
            display: inline-flex;
            justify-content: center;
            align-items: center;
        }

        .btn-group .btn {
            border-radius: 0;
        }

        .btn-group .btn:first-child {
            border-top-left-radius: 0.375rem;
            border-bottom-left-radius: 0.375rem;
        }

        .btn-group .btn:last-child {
            border-top-right-radius: 0.375rem;
            border-bottom-right-radius: 0.375rem;
        }

        /* Animación para alertas */
        .alert {
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <script>
        function confirmarCambioRol() {
            const switchElement = document.getElementById('rolSwitch');
            const isChecked = switchElement.checked;
            const nuevoRol = isChecked ? 'Administrador' : 'Empleado';
            const rolActual = isChecked ? 'Empleado' : 'Administrador';
            
            const mensaje = `¿Estás seguro de que quieres cambiar el rol de ${rolActual} a ${nuevoRol}?\n\n` +
                          (isChecked ? 
                              'Como Administrador tendrá acceso completo al sistema.' : 
                              'Como Empleado tendrá acceso limitado al sistema.');
            
            if (confirm(mensaje)) {
                document.getElementById('toggleRolForm').submit();
            } else {
                // Revertir el switch si el usuario cancela
                switchElement.checked = !isChecked;
            }
        }
    </script>

    <!-- Modal Recibo Consolidado -->
    <div class="modal fade" id="reciboConsolidadoModal" tabindex="-1" aria-labelledby="reciboConsolidadoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title" id="reciboConsolidadoModalLabel">
                        <i class="bi bi-receipt me-2"></i>Generar recibo de comisiones
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form action="{{ route('comisiones.reciboConsolidado') }}" method="GET" target="_blank" onsubmit="setTimeout(() => $('#reciboConsolidadoModal').modal('hide'), 100)">
                    <div class="modal-body p-4">
                        <input type="hidden" name="empleado_id" value="{{ $empleado->id }}">
                        
                        <div class="alert alert-light border">
                            <i class="bi bi-person-badge text-primary me-2"></i>
                            <strong>Asesor:</strong> {{ $empleado->nombre }} {{ $empleado->apellido }}
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fecha_inicio" class="form-label fw-bold">Fecha de Inicio</label>
                                    <div class="input-group">
                                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required max="{{ date('Y-m-d') }}">
                                        <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('fecha_inicio').value = '{{ date('Y-m-d') }}'">Hoy</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fecha_fin" class="form-label fw-bold">Fecha Fin</label>
                                    <div class="input-group">
                                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required value="{{ date('Y-m-d') }}">
                                        <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('fecha_fin').value = '{{ date('Y-m-d') }}'">Hoy</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @php 
                            $comisionesEmp = \App\Models\Comisione::where('empleado_id', $empleado->id)
                                ->select('tipo_comision', 'estado')
                                ->whereNotNull('tipo_comision')
                                ->where('tipo_comision', '!=', '')
                                ->where('tipo_comision', '!=', 'PARCIALIDAD')
                                ->get();

                            $tiposPagadasEmp = $comisionesEmp->filter(function($c) {
                                return in_array(ucfirst(strtolower($c->estado)), ['Pagada', 'Entregada', 'Hecho']);
                            })->pluck('tipo_comision')->unique()->values();

                            $tiposTodasEmp = $comisionesEmp->pluck('tipo_comision')->unique()->values();
                        @endphp

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="incluir_pendientes"
                                    name="incluir_pendientes" value="1">
                                <label class="form-check-label fw-bold text-muted small" for="incluir_pendientes">Mostrar
                                    sección de comisiones pendientes</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="tipo_comision" class="form-label fw-bold">Tipo de Comisión</label>
                            <select class="form-select" id="tipo_comision" name="tipo_comision">
                                <option value="">Todas las comisiones</option>
                                @foreach($tiposPagadasEmp as $tipo)
                                    <option value="{{ $tipo }}">{{ ucfirst($tipo) }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const tiposPagadasEmp = {!! json_encode($tiposPagadasEmp) !!};
                            const tiposTodasEmp = {!! json_encode($tiposTodasEmp) !!};
                            
                            const tipoComisionSelect = document.getElementById('tipo_comision');
                            const incluirPendientesSwitch = document.getElementById('incluir_pendientes');
                            
                            if (tipoComisionSelect && incluirPendientesSwitch) {
                                incluirPendientesSwitch.addEventListener('change', function() {
                                    let tipos = this.checked ? tiposTodasEmp : tiposPagadasEmp;
                                    const currentValue = tipoComisionSelect.value;
                                    
                                    tipoComisionSelect.innerHTML = '<option value="">Todas las comisiones</option>';
                                    
                                    tipos.forEach(tipo => {
                                        const option = document.createElement('option');
                                        option.value = tipo;
                                        option.textContent = tipo.charAt(0).toUpperCase() + tipo.slice(1);
                                        if (tipo === currentValue) option.selected = true;
                                        tipoComisionSelect.appendChild(option);
                                    });
                                });
                            }
                        });
                        </script>
                    </div>
                    <div class="modal-footer border-0 bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-printer me-1"></i>Generar Recibo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal: Actividad de Rutas --}}
    <div class="modal fade" id="actividadRutasModal" tabindex="-1" aria-labelledby="actividadRutasModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="actividadRutasModalLabel">
                            <i class="bi bi-signpost-split me-2" style="color:#79481D;"></i>Actividad de Rutas
                        </h5>
                        <p class="text-muted small mb-0" id="actividadRutasSub">Historial de movimientos del cobrador</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body pt-3">
                    <div id="actividadRutasLoading" class="text-center py-5 text-muted">
                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                        Cargando actividad...
                    </div>
                    <div id="actividadRutasEmpty" class="text-center py-5 d-none">
                        <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                        <p class="text-muted mb-0">Aún no hay movimientos de rutas registrados.</p>
                    </div>
                    <div id="actividadRutasList" class="d-none"></div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .ruta-act-month {
            position: sticky; top: 0; z-index: 1;
            background: #f8f9fa; border-radius: 10px;
            padding: 8px 12px; margin: 16px 0 10px;
            font-size: 0.8rem; font-weight: 700; color: #79481D;
            letter-spacing: 0.02em; text-transform: capitalize;
        }
        .ruta-act-month:first-child { margin-top: 0; }
        .ruta-act-item {
            display: flex; gap: 12px; align-items: flex-start;
            padding: 12px 4px; border-bottom: 1px solid rgba(60,60,67,0.1);
        }
        .ruta-act-item:last-child { border-bottom: none; }
        .ruta-act-time {
            flex-shrink: 0; width: 64px; text-align: right;
            font-size: 0.72rem; color: #8e8e93; line-height: 1.3; padding-top: 2px;
        }
        .ruta-act-body { min-width: 0; flex: 1; }
        .ruta-act-title { font-size: 0.92rem; font-weight: 600; color: #1c1c1e; margin: 0 0 2px; }
        .ruta-act-detail { font-size: 0.78rem; color: #636366; margin: 0; }
        .ruta-act-meta { font-size: 0.72rem; color: #8e8e93; margin-top: 4px; }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var btn = document.getElementById('btnActividadRutas');
        var modalEl = document.getElementById('actividadRutasModal');
        if (!btn || !modalEl) return;

        var loading = document.getElementById('actividadRutasLoading');
        var empty = document.getElementById('actividadRutasEmpty');
        var list = document.getElementById('actividadRutasList');
        var sub = document.getElementById('actividadRutasSub');
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        var loadedFor = null;

        function money(n) {
            if (n == null) return '';
            return '$' + Number(n).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function render(data) {
            if (sub && data.empleado) {
                sub.textContent = data.empleado.nombre + ' · ' + (data.total || 0) + ' movimiento' + (data.total === 1 ? '' : 's');
            }
            loading.classList.add('d-none');
            if (!data.grupos || !data.grupos.length) {
                empty.classList.remove('d-none');
                list.classList.add('d-none');
                list.innerHTML = '';
                return;
            }
            empty.classList.add('d-none');
            list.classList.remove('d-none');
            var html = '';
            data.grupos.forEach(function(grupo) {
                html += '<div class="ruta-act-month"><i class="bi bi-calendar3 me-1"></i>' + grupo.label + '</div>';
                grupo.items.forEach(function(item) {
                    html += '<div class="ruta-act-item">';
                    html += '<div class="ruta-act-time">' + (item.fecha || '') + '<br>' + (item.hora || '') + '</div>';
                    html += '<div class="ruta-act-body">';
                    html += '<div class="d-flex flex-wrap align-items-center gap-2 mb-1">';
                    html += '<span class="badge ' + item.badge + '">' + item.tipo_label + '</span>';
                    if (item.monto_total != null) {
                        html += '<span class="badge bg-light text-dark border">' + money(item.monto_total) + '</span>';
                    }
                    html += '</div>';
                    html += '<p class="ruta-act-title">' + (item.titulo || '') + '</p>';
                    if (item.detalle) html += '<p class="ruta-act-detail">' + item.detalle + '</p>';
                    if (item.ruta_nombre) {
                        html += '<div class="ruta-act-meta"><i class="bi bi-signpost me-1"></i>' + item.ruta_nombre + '</div>';
                    }
                    html += '</div></div>';
                });
            });
            list.innerHTML = html;
        }

        btn.addEventListener('click', function() {
            var url = btn.getAttribute('data-url');
            modal.show();
            if (loadedFor === url && list.innerHTML) {
                loading.classList.add('d-none');
                return;
            }
            loading.classList.remove('d-none');
            empty.classList.add('d-none');
            list.classList.add('d-none');
            fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.json().then(function(data) { return { ok: r.ok, data: data }; }); })
            .then(function(res) {
                if (!res.ok) throw new Error((res.data && res.data.message) || 'No se pudo cargar la actividad.');
                loadedFor = url;
                render(res.data);
            })
            .catch(function(err) {
                loading.classList.add('d-none');
                empty.classList.remove('d-none');
                empty.innerHTML = '<i class="bi bi-exclamation-triangle fs-1 text-warning d-block mb-2"></i>' +
                    '<p class="text-muted mb-0">' + (err.message || 'Error al cargar.') + '</p>';
            });
        });
    });
    </script>
    @endif
@endsection
