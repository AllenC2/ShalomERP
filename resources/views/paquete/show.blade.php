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
                                    <div
                                        class="p-3 bg-success bg-opacity-10 border border-success border-opacity-25 rounded">
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
                                                        <i class="bi bi-calendar-plus me-1"></i>Creado: <span
                                                            class="fw-bold">{{ $paquete->created_at->translatedFormat('d \d\e F \d\e Y') }}</span>
                                                    </small>
                                                </div>

                                                <div>
                                                    <small class="d-block text-muted">
                                                        <i class="bi bi-clock me-1"></i>Actualizado: <span
                                                            class="fw-bold">{{ $paquete->updated_at->diffForHumans() }}</span>
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
                                            <!-- Resumen Financiero Consolidado -->
                                            @php
                                                $totalComisionPorcentaje = $paquete->porcentajes->where('modo_comision', 'porcentaje')->sum('cantidad_porcentaje');
                                                $totalComisionMonto = $paquete->porcentajes->where('modo_comision', 'monto')->sum('monto_fijo');
                                                $montoComisionTotal = (($paquete->precio * $totalComisionPorcentaje) / 100) + $totalComisionMonto;
                                                $utilidad = $paquete->precio - $montoComisionTotal;
                                            @endphp

                                            <div class="mt-3 p-3 bg-white bg-opacity-50 rounded border">
                                                <h6 class="text-muted text-center small fw-bold mb-3 text-uppercase">Resumen
                                                    Financiero</h6>

                                                <div class="row g-2 mb-3 text-center">
                                                    @foreach($paquete->porcentajes as $porcentaje)
                                                        <div class="col-6 mb-1">
                                                            <div class="p-1 bg-secondary bg-opacity-10 rounded">
                                                                <small class="text-muted d-block"
                                                                    style="font-size: 0.7em;">{{ ucfirst($porcentaje->tipo_porcentaje) }}</small>
                                                                <span class="fw-bold" style="font-size: 0.9em;">
                                                                    @if($porcentaje->modo_comision === 'monto')
                                                                        ${{ number_format($porcentaje->monto_fijo, 2) }}
                                                                    @else
                                                                        {{ $porcentaje->cantidad_porcentaje }}%
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                <div class="pt-2 border-top">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <small class="text-muted">Inversión de Comisiones:</small>
                                                        <span
                                                            class="fw-bold text-danger">-${{ number_format($montoComisionTotal, 2) }}</span>
                                                    </div>
                                                    <div
                                                        class="d-flex justify-content-between align-items-center p-2 bg-primary bg-opacity-10 rounded mt-2">
                                                        <span class="fw-bold text-primary">Utilidad Estimada:</span>
                                                        <span class="fw-bold text-primary"
                                                            style="font-size: 1.1em;">${{ number_format($utilidad, 2) }}</span>
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
                                                                    <br><small
                                                                        class="text-muted">{{ $contrato->cliente->email }}</small>
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
                                                                <small
                                                                    class="text-muted">{{ number_format($porcentajePagado, 1) }}%</small>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @php
                                                            $badgeClass = match ($contrato->estado) {
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
                                                                class="btn btn-outline-primary btn-sm" title="Ver detalles">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                            <a href="{{ route('contratos.edit', $contrato->id) }}"
                                                                class="btn btn-outline-success btn-sm" title="Editar">
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
                                                    <div>
                                                        <strong>{{ $paquete->contratos->where('estado', 'activo')->count() }}</strong>
                                                    </div>
                                                    <small>Activos</small>
                                                </div>
                                                <div class="col-md-3">
                                                    <i class="bi bi-currency-dollar fs-4 text-success"></i>
                                                    @php
                                                        $totalPagadoGeneral = $paquete->contratos->sum(function ($contrato) {
                                                            return calcularMontoPagadoContrato($contrato->pagos);
                                                        });
                                                    @endphp
                                                    <div><strong>${{ number_format($totalPagadoGeneral, 2) }}</strong></div>
                                                    <small>Total Pagado</small>
                                                </div>
                                                <div class="col-md-3">
                                                    <i class="bi bi-wallet2 fs-4 text-warning"></i>
                                                    @php
                                                        $saldoPendiente = $paquete->contratos->where('estado', 'activo')->sum(function ($contrato) {
                                                            return $contrato->monto_total - calcularMontoPagadoContrato($contrato->pagos);
                                                        });
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