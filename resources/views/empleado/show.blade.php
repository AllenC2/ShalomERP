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
                                        Empleado desde: {{ $empleado->created_at->format('d') }} de
                                        {{ ucfirst($empleado->created_at->locale('es')->monthName) }} de
                                        {{ $empleado->created_at->format('Y') }}
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
                                @if($empleado->user->email)
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
                                                {{ ucfirst($empleado->created_at->locale('es')->monthName) }} {{ $empleado->created_at->format('Y') }}
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
                            $tiempoEmpleado = $empleado->created_at->locale('es')->diffForHumans(null, true);
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
                            <a href="{{ route('empleados.edit', $empleado->id) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-pencil-square me-1"></i> Editar
                            </a>
                            @if($empleado->estado === 'activo')
                                <form action="{{ route('empleados.darDeBaja', $empleado->id) }}" method="POST" class="d-inline" 
                                      onsubmit="return confirm('¿Estás seguro de que quieres dar de baja a este empleado?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="bi bi-person-x me-1"></i> Dar de baja
                                    </button>
                                </form>
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
                    </div>

                    <!-- Gestión de rol de usuario -->
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
                                                            {{ \Carbon\Carbon::parse($comision->fecha_comision)->locale('es_MX')->isoFormat('D [de] MMMM [de] YYYY') }}
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
        .btn-group form {
            flex: 1;
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
    @endif
@endsection
