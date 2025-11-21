@extends('layouts.app')

@section('template_title')
    {{ $paquete->name ?? __('Show') . " " . __('Paquete') }}
@endsection

@section('content')
    <section class="content container-fluid">
    
     <div class="container py-2">
        <!-- Header del paquete -->
        <div class="package-header">
            <a href="{{ route('paquetes.index') }}" class="modern-link mb-3 d-inline-block">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('Regresar') }}
            </a>
            <div class="row align-items-center">
                <div class="col-md-12">
                    <!-- Header moderno -->
                    <div class="page-header">
                        <div class="header-content">
                            <div class="header-icon">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <div class="header-text">
                                <h1 class="page-title">Paquete {{ ucfirst(strtolower($paquete->nombre)) }}</h1>
                                <p class="page-subtitle">Vista general del paquete</p>
                            </div>
                        </div>
                        <div class="header-actions">
                            <div class="text-md-end">
                                <span class="badge status-badge bg-primary">
                                    ACTIVO
                                </span>
                                <p class="text-muted mb-0 mt-2">
                                    Creado: {{ $paquete->created_at->translatedFormat('d \d\e F \d\e Y') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">

            <!-- Columna derecha - Información adicional -->
            <div class="col-lg-4">
             
                <div class="mb-4">
                    <div class="info-section">
                        <h6 class="text-muted text-uppercase small fw-bold mb-3">
                            <i class="bi bi-box-seam me-2"></i>Detalles del Paquete
                        </h6>

                        <div class="row g-3">
                            <div class="col-12">
                                <div class="p-3 bg-success bg-opacity-10 border border-success border-opacity-25 rounded">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="flex-grow-1">
                                            <div class="mb-2">
                                                <span class="fw-bold mb-0" style="font-weight:900; font-size:1.8em;">
                                                    {{ $paquete->nombre }}
                                                </span>
                                            </div>
                                            
                                            @if($paquete->descripcion)
                                            <div class="mb-2">
                                                <small class="d-block text-muted me-4">
                                                    {{ $paquete->descripcion }}
                                                </small>
                                            </div>
                                            @endif

                                            <div class="mb-1">
                                                <small class="d-block text-muted">
                                                    <i class="bi bi-calendar-plus me-1"></i>Creado: <span class="fw-bold">{{ $paquete->created_at->translatedFormat('d \d\e F \d\e Y') }}</span>
                                                </small>
                                            </div>
                                            
                                            <div>
                                                <small class="d-block text-muted">
                                                    <i class="bi bi-clock me-1"></i>Actualizado: <span class="fw-bold">{{ $paquete->updated_at->diffForHumans() }}</span>
                                                </small>
                                            </div>
                                        </div>

                                        <div class="text-end">
                                            <div class="text-center">
                                                <!-- Precio debajo del icono -->
                                                <div class="mt-2">
                                                    <span class="badge bg-success text-white" style="font-size: 14px;">
                                                        ${{ number_format($paquete->precio, 2) }}
                                                    </span>
                                                    <small class="text-muted d-block" style="font-size: 10px;">
                                                        Precio base
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @if($paquete->porcentajes && $paquete->porcentajes->count() > 0)
                                    <!-- Resumen de comisiones -->
                                    <div class="mt-3 p-2 bg-white bg-opacity-50 rounded border">
                                        <div class="row text-center">
                                            <div class="col-12 mb-2">
                                                <small class="text-muted fw-bold">Comisiones Configuradas</small>
                                            </div>

                                            <div class="col-12">
                                                <div class="row">
                                                    @foreach($paquete->porcentajes as $porcentaje)
                                                        <div class="col-md-4 mb-2">
                                                            <div class="d-flex flex-column text-sm">
                                                                <small class="text-muted">{{ ucfirst($porcentaje->tipo_porcentaje) }}</small>
                                                                <small class="fw-bold">{{ $porcentaje->cantidad_porcentaje }}%</small>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                            @php
                                                $totalComision = $paquete->porcentajes->sum('cantidad_porcentaje');
                                                $montoComisionTotal = ($paquete->precio * $totalComision) / 100;
                                            @endphp

                                        </div>
                                    </div>
                                    <div class="col-12 mt-2">
                                                <div class="bg-primary bg-opacity-10 rounded p-2">
                                                    <div class="text-center">
                                                        <small class="text-muted">Total comisiones</small>
                                                        <div class="row mt-1">
                                                            <div class="col-6 text-center">
                                                                <small class="text-muted d-block">Porcentaje total</small>
                                                                <strong class="text-primary">{{ $totalComision }}%</strong>
                                                            </div>
                                                            <div class="col-6 text-center">
                                                                <small class="text-muted d-block">Monto estimado</small>
                                                                <strong class="text-primary">${{ number_format($montoComisionTotal, 2) }}</strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>    

            <!-- Columna izquierda - Detalles del paquete -->
            <div class="col-lg-8">
                <h6 class="text-muted text-uppercase small fw-bold mb-3">
                    <i class="bi bi-file-earmark-text me-2"></i>Contratos del Paquete
                </h6>
                <div class="card mb-4">
                    <div class="card-body">
                        @if($paquete->contratos && $paquete->contratos->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Cliente</th>
                                            <th>Total Pagado</th>
                                            <th>Estado</th>
                                            <th>Fecha Inicio</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($paquete->contratos as $contrato)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-secondary">#{{ $contrato->id }}</span>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-person-circle me-2 text-primary"></i>
                                                        <div>
                                                            <strong>{{ $contrato->cliente->nombre ?? 'N/A' }}</strong>
                                                            @if($contrato->cliente->email)
                                                                <br><small class="text-muted">{{ $contrato->cliente->email }}</small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @php
                                                        $totalPagado = calcularMontoPagadoContrato($contrato->pagos);
                                                    @endphp
                                                    <div class="text-center">
                                                        <span class="fw-bold text-success">
                                                            ${{ number_format($totalPagado, 2) }} MXN
                                                        </span>
                                                        <br>
                                                        <small class="text-muted">
                                                            de ${{ number_format($contrato->monto_total, 2) }}
                                                        </small>
                                                        @if($contrato->monto_total > 0)
                                                            <br>
                                                            @php
                                                                $porcentajePagado = ($totalPagado / $contrato->monto_total) * 100;
                                                            @endphp
                                                            <div class="progress mt-1" style="height: 4px;">
                                                                <div class="progress-bar bg-success" role="progressbar" 
                                                                     style="width: {{ min(100, $porcentajePagado) }}%"></div>
                                                            </div>
                                                            <small class="text-muted">{{ number_format($porcentajePagado, 1) }}%</small>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    @php
                                                        $badgeClass = match($contrato->estado) {
                                                            'activo' => 'bg-success',
                                                            'finalizado' => 'bg-primary',
                                                            'cancelado' => 'bg-danger',
                                                            'suspendido' => 'bg-warning text-dark',
                                                            default => 'bg-secondary'
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $badgeClass }}">
                                                        {{ ucfirst($contrato->estado ?? 'Desconocido') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <i class="bi bi-calendar-event me-1 text-muted"></i>
                                                    {{ \Carbon\Carbon::parse($contrato->fecha_inicio)->format('d/m/Y') }}
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('contratos.show', $contrato->id) }}" 
                                                           class="btn btn-outline-primary btn-sm" 
                                                           title="Ver detalles">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <a href="{{ route('contratos.edit', $contrato->id) }}" 
                                                           class="btn btn-outline-success btn-sm" 
                                                           title="Editar">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Resumen de contratos -->
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="alert alert-info">
                                        <div class="row text-center">
                                            <div class="col-md-3">
                                                <i class="bi bi-file-earmark-text fs-4 text-primary"></i>
                                                <div><strong>{{ $paquete->contratos->count() }}</strong></div>
                                                <small>Total Contratos</small>
                                            </div>
                                            <div class="col-md-3">
                                                <i class="bi bi-check-circle fs-4 text-success"></i>
                                                <div><strong>{{ $paquete->contratos->where('estado', 'activo')->count() }}</strong></div>
                                                <small>Activos</small>
                                            </div>
                                            <div class="col-md-3">
                                                <i class="bi bi-currency-dollar fs-4 text-success"></i>
                                                @php
                                                    $totalPagadoGeneral = $paquete->contratos->sum(function($contrato) {
                                                        return calcularMontoPagadoContrato($contrato->pagos);
                                                    });
                                                @endphp
                                                <div><strong>${{ number_format($totalPagadoGeneral, 2) }}</strong></div>
                                                <small>Total Pagado</small>
                                            </div>
                                            <div class="col-md-3">
                                                <i class="bi bi-wallet2 fs-4 text-warning"></i>
                                                @php
                                                    $montoTotalContratos = $paquete->contratos->sum('monto_total');
                                                    $saldoPendiente = $montoTotalContratos - $totalPagadoGeneral;
                                                @endphp
                                                <div><strong>${{ number_format($saldoPendiente, 2) }}</strong></div>
                                                <small>Saldo Pendiente</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-file-earmark-x text-muted" style="font-size: 4rem;"></i>
                                <h5 class="text-muted mt-3">No hay contratos asociados</h5>
                                <p class="text-muted">Este paquete aún no tiene contratos relacionados.</p>
                                <a href="{{ route('contratos.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-2"></i>Crear Primer Contrato
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            
        </div>
    </div>
    </section>
@endsection
