@extends('layouts.app')

@section('template_title')
{{ $contrato->name ?? __('Show') . " " . __('Contrato') }}
@endsection

@section('content')
<section class="content container-fluid">

    <div class="container py-2">
        <!-- Header del contrato -->
        <div class="contract-header">
            <a href="{{ url()->previous() }}" class="modern-link mb-3 d-inline-block">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('Regresar') }}
            </a>
            <div class="row align-items-center">
                <div class="col-md-12">
                    <!-- Header moderno -->
                    <div class="page-header">
                        <div class="header-content">
                            <div class="header-icon">
                                <i class="bi bi-pencil-square"></i>
                            </div>
                            <div class="header-text">
                                <h1 class="page-title">Contrato {{$contrato->paquete->nombre}}#{{$contrato->id}}</h1>
                                <p class="page-subtitle">Vista general del contrato</p>
                            </div>
                        </div>
                        <div class="header-actions">
                            <div class="text-md-end">
                                @php
                                    $estadoPagos = $contrato->estado_pagos;
                                @endphp
                                <div class="payment-status-info">
                                    @if($estadoPagos['tiene_vencidas'])
                                        <div class="badge bg-danger p-2 mb-2" style="font-size: 0.85rem;">
                                            <div class="fw-bold">
                                                <i class="bi bi-exclamation-triangle me-1"></i>
                                                {{ $estadoPagos['cuotas_vencidas'] }} cuota{{ $estadoPagos['cuotas_vencidas'] > 1 ? 's' : '' }} retrasada{{ $estadoPagos['cuotas_vencidas'] > 1 ? 's' : '' }}
                                            </div>
                                            <small>
                                                Total: ${{ number_format($estadoPagos['monto_vencido'], 2) }}
                                                @if($estadoPagos['dias_retraso'])
                                                    <br>{{ intval($estadoPagos['dias_retraso']) }} día{{ intval($estadoPagos['dias_retraso']) > 1 ? 's' : '' }} de retraso
                                                @endif
                                            </small>
                                        </div>
                                    @elseif($estadoPagos['tiene_en_tolerancia'])
                                        <div class="badge bg-warning bg-opacity-25 text-warning-emphasis border border-warning p-2 mb-2" style="font-size: 0.85rem;">
                                            <div class="fw-bold text-start">
                                                <i class="bi bi-clock me-1"></i>
                                                {{ $estadoPagos['cuotas_en_tolerancia'] }} cuota{{ $estadoPagos['cuotas_en_tolerancia'] > 1 ? 's' : '' }} en período de gracia
                                                <small>
                                                    <br>Tolerancia: {{ $estadoPagos['tolerancia_dias'] }} día{{ $estadoPagos['tolerancia_dias'] > 1 ? 's' : '' }}
                                                </small>
                                            </div>
                                        </div>
                                    @else
                                        <div class="badge bg-success p-2 mb-2" style="font-size: 0.85rem;">
                                            <div class="fw-bold">
                                                <i class="bi bi-check-circle me-1"></i>
                                                Todas las cuotas al día
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <p class="text-muted mb-0 mt-2">
                                    Creado: {{ $contrato->created_at->format('d') }} de {{ ucfirst($contrato->created_at->locale('es')->monthName) }} de {{ $contrato->created_at->format('Y') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Columna izquierda -->
            @if(Auth::check() && (Auth::user()->role === 'admin' ))
            <div class="col-lg-8">
            @else
            <div class="col-lg-12">
            @endif
                @if(Auth::check() && (Auth::user()->role === 'admin' || Auth::user()->role === 'empleado'))
                <!--  Detalles del contrato -->
                <div class="card mb-4">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="bi bi-file-earmark-text me-2" style="color: #79481D;"></i>Detalles del Contrato</h5>
                        <span class="badge bg-primary">{{ strtoupper($contrato->estado) }}</span>
                    </div>
                    <div class="card-body">

                        <!-- Información Principal -->
                        <div class="row mb-4">
                            <!-- Información del Cliente y Fechas -->
                            <div class="col-md-6">
                                <div class="info-section">
                                    <h6 class="text-muted text-uppercase small fw-bold mb-3">
                                        <i class="bi bi-person-circle me-2"></i>Información del Cliente
                                    </h6>
                                    <div class="mb-3 p-0">
                                        @if(Auth::check() && Auth::user()->role === 'admin')
                                            <a href="{{ route('clientes.show', $contrato->cliente->id) }}" class="text-decoration-none">
                                        @else
                                            <a class="text-decoration-none hover-none">
                                        @endif
                                            <div class="card border-0 shadow-sm h-100 hover-card" style="background: linear-gradient(135deg, #e3f2fd 0%, #f8f9fa 100%);">
                                                <div class="card-body p-4">
                                                    <!-- Header del cliente -->
                                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                                        <div class="d-flex align-items-center">
                                                            <div class="client-avatar me-3">
                                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 1.5em; font-weight: bold;">
                                                                    {{ strtoupper(substr($contrato->cliente->nombre, 0, 1) . substr($contrato->cliente->apellido, 0, 1)) }}
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <h5 class="fw-bold mb-1 text-dark">
                                                                    {{ $contrato->cliente->nombre }} {{ $contrato->cliente->apellido }}
                                                                </h5>
                                                                <div class="d-flex gap-2 align-items-center">
                                                                    <span class="badge bg-light text-dark border">Cliente</span>
                                                                    @if($contrato->cliente->tipo)
                                                                    <span class="badge bg-info text-dark">{{ ucfirst($contrato->cliente->tipo) }}</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="text-end">
                                                            <i class="bi bi-arrow-right text-muted"></i>
                                                        </div>
                                                    </div>

                                                    <!-- Información de contacto -->
                                                    <div class="row g-3 mb-3">
                                                        @if($contrato->cliente->email)
                                                        <div class="col-12">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <i class="bi bi-envelope text-primary"></i>
                                                                <span class="text-muted small">{{ $contrato->cliente->email }}</span>
                                                            </div>
                                                        </div>
                                                        @endif

                                                        @if($contrato->cliente->telefono)
                                                        <div class="col-md-12">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <i class="bi bi-telephone text-success"></i>
                                                                <span class="text-muted small">{{ $contrato->cliente->telefono }}</span>
                                                            </div>
                                                        </div>
                                                        @endif

                                                        @if($contrato->cliente->domicilio_completo)
                                                        <div class="col-md-12">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <i class="bi bi-geo-alt text-warning"></i>
                                                                <span class="text-muted small">{{ Str($contrato->cliente->domicilio_completo) }}</span>
                                                            </div>
                                                        </div>
                                                        @endif
                                                    </div>

                                                    <!-- Estadísticas del cliente -->
                                                    <div class="border-top pt-3">
                                                        <div class="row text-center">
                                                            <div class="col-4">
                                                                <div class="client-stat">
                                                                    <i class="bi bi-calendar-plus text-info d-block fs-5 mb-1"></i>
                                                                    <small class="text-muted d-block">Cliente desde</small>
                                                                    <small class="fw-bold">
                                                                        {{ ucfirst($contrato->cliente->created_at->locale('es')->monthName) }} {{ $contrato->cliente->created_at->format('Y') }}
                                                                    </small>
                                                                </div>
                                                            </div>
                                                            <div class="col-4">
                                                                <div class="client-stat">
                                                                    <i class="bi bi-file-earmark-text text-success d-block fs-5 mb-1"></i>
                                                                    <small class="text-muted d-block">Contratos</small>
                                                                    <small class="fw-bold">{{ $contratos_cliente }}</small>
                                                                </div>
                                                            </div>
                                                            <div class="col-4">
                                                                <div class="client-stat">
                                                                    <i class="bi bi-check-circle text-primary d-block fs-5 mb-1"></i>
                                                                    <small class="text-muted d-block">Activos</small>
                                                                    <small class="fw-bold">{{ $contratos_activos_cliente }}</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Indicador de tiempo como cliente -->
                                                    @php
                                                    $tiempoCliente = $contrato->cliente->created_at->locale('es')->diffForHumans(null, true);
                                                    $añosCliente = $contrato->cliente->created_at->diffInYears(now());
                                                    @endphp
                                                    <div class="mt-3">
                                                        <div class="border border-muted rounded p-2 text-center">
                                                            <small class="text-muted">
                                                                <i class="bi bi-star-fill text-warning me-1"></i>
                                                                @if($tiempoCliente)
                                                                Cliente registrado hace {{ $tiempoCliente }}
                                                                @else
                                                                No sabemos desde cuándo esta registrado como cliente
                                                                @endif
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>

                                </div>
                            </div>

                            <!-- Información Financiera -->
                            <div class="col-md-6">
                                <div class="info-section">
                                    <h6 class="text-muted text-uppercase small fw-bold mb-3">
                                        <i class="bi bi-currency-dollar me-2"></i>aInformación Financiera
                                    </h6>

                                    <div class="row g-3">
                                        <div class="col-12">
                                            <div class="p-3 bg-success bg-opacity-10 border border-success border-opacity-25 rounded">
                                                <div class="d-flex align-items-start justify-content-between">
                                                    <div>
                                                        <div class="mb-1">
                                                            <span class="fw-bold mb-0" style="font-weight:900; font-size:2em;">
                                                                {{ $contrato->paquete->nombre }}
                                                            </span>
                                                        </div>
                                                        <div class="mb-1">
                                                            <small class="d-block text-muted">
                                                                <i class="bi bi-calendar-event me-1"></i>Inicio: <span class="fw-bold">{{ \Carbon\Carbon::parse($contrato->fecha_inicio)->locale('es')->translatedFormat('d \d\e F \d\e Y') }}</span>
                                                            </small>
                                                        </div>
                                                        <div>
                                                            <small class="d-block text-muted">
                                                                <i class="bi bi-calendar-check me-1"></i>Fin: <span class="fw-bold">{{ $contrato->fecha_fin ? \Carbon\Carbon::parse($contrato->fecha_fin)->locale('es')->translatedFormat('d \d\e F \d\e Y') : 'Indefinido' }}</span>
                                                            </small>
                                                        </div>
                                                    </div>
                                                    @php
                                                    // Calcular cuotas (solo pagos de tipo "cuota")
                                                    $cuotasPagadas = $pagos_contrato->filter(function($pago) {
                                                        return $pago->estado == 'hecho' && 
                                                               strtolower($pago->tipo_pago ?? '') == 'cuota';
                                                    })->count();

                                                    $totalCuotas = $contrato->numero_cuotas ?? 0;
                                                    $porcentajeCuotas = $totalCuotas > 0 ? ($cuotasPagadas / $totalCuotas) * 100 : 0;
                                                    $circumference = 2 * pi() * 45; // radio = 45
                                                    $strokeDasharray = $circumference;
                                                    $strokeDashoffset = $circumference - ($porcentajeCuotas / 100) * $circumference;
                                                    @endphp

                                                    <div class="text-end">

                                                        <!-- Gráfico circular de progreso -->
                                                        <div class="text-center">
                                                            <div class="position-relative d-inline-block">
                                                                <svg width="80" height="80" class="progress-ring">
                                                                    <!-- Círculo de fondo -->
                                                                    <circle cx="40" cy="40" r="35"
                                                                        fill="none"
                                                                        stroke="#d1d5db"
                                                                        stroke-width="6" />
                                                                    <!-- Círculo de progreso -->
                                                                    <circle cx="40" cy="40" r="35"
                                                                        fill="none"
                                                                        stroke="#198754"
                                                                        stroke-width="6"
                                                                        stroke-linecap="round"
                                                                        stroke-dasharray="{{ 2 * pi() * 35 }}"
                                                                        stroke-dashoffset="{{ 2 * pi() * 35 - ($porcentajeCuotas / 100) * 2 * pi() * 35 }}"
                                                                        transform="rotate(-90 40 40)"
                                                                        style="transition: stroke-dashoffset 0.5s ease-in-out" />
                                                                </svg>
                                                                <!-- Texto en el centro del círculo -->
                                                                <div class="position-absolute top-50 start-50 translate-middle text-center">
                                                                    <div class="fw-bold text-success" style="font-size: 14px; line-height: 1;">
                                                                        {{ $cuotasPagadas }}/{{ $totalCuotas }}
                                                                        <small class="text-muted">cuotas</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- Leyenda debajo del gráfico -->
                                                            <div class="mt-2">
                                                                <small class="text-muted d-block" style="font-size: 10px;">
                                                                    {{ round($porcentajeCuotas) }}% de cuotas pagadas
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                @php
                                                $montoInicial = $contrato->monto_inicial ?? 0;
                                                $montoBonificacion = $contrato->monto_bonificacion ?? 0;
                                                $montoFinanciado = $contrato->monto_total - $montoInicial - $montoBonificacion;
                                                $cuotaCalculada = $contrato->numero_cuotas > 0 ? $montoFinanciado / $contrato->numero_cuotas : 0;
                                                @endphp

                                                <!-- Resumen de cálculo de cuotas -->
                                                <div class="mt-3 p-2 bg-white bg-opacity-50 rounded border">
                                                    <div class="row text-center">
                                                        <div class="col-12 mb-2">
                                                            <small class="text-muted fw-bold">Desglose del Financiamiento</small>
                                                        </div>

                                                        <div class="col-12 mb-2">
                                                            <div class="d-flex justify-content-between align-items-center text-sm">
                                                                <small class="text-muted">Monto Total:</small>
                                                                <small class="fw-bold">${{ number_format($contrato->monto_total, 2) }}</small>
                                                            </div>
                                                            @if($montoInicial > 0)
                                                            <div class="d-flex justify-content-between align-items-center text-sm">
                                                                <small class="text-muted">(-) Inicial:</small>
                                                                <small class="text-warning">-${{ number_format($montoInicial, 2) }}</small>
                                                            </div>
                                                            @endif
                                                            @if($montoBonificacion > 0)
                                                            <div class="d-flex justify-content-between align-items-center text-sm">
                                                                <small class="text-muted">(-) Bonificación:</small>
                                                                <small class="text-info">-${{ number_format($montoBonificacion, 2) }}</small>
                                                            </div>
                                                            @endif
                                                            <hr class="my-1">
                                                            <div class="d-flex justify-content-between align-items-center text-sm">
                                                                <small class="text-muted fw-bold">Monto a Financiar:</small>
                                                                <small class="fw-bold text-success">${{ number_format($montoFinanciado, 2) }}</small>
                                                            </div>
                                                        </div>

                                                        <div class="col-12">
                                                            <div class="bg-primary bg-opacity-10 rounded p-2">
                                                                <div class="text-center">
                                                                    <small class="text-muted">{{ $contrato->numero_cuotas }} pagos programados</small>
                                                                </div>
                                                                <div class="row mt-1">
                                                                    <div class="col-6 text-center">
                                                                        <small class="text-muted d-block">Por cuotas de</small>
                                                                        <strong class="text-primary">${{ number_format($cuotaCalculada, 2) }}</strong>
                                                                    </div>
                                                                    <div class="col-6 text-center">
                                                                        <small class="text-muted d-block">Cada</small>
                                                                        <strong class="text-primary">{{ ucfirst($contrato->frecuencia_cuotas ?? 'N/A') }} días</strong>
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
                            </div>
                        </div>


                    </div>
                </div>
                @endif

                <!-- Siguiente Cuota Pendiente -->
                @php
                // Buscar la siguiente cuota pendiente de tipo "cuota"
                $siguienteCuota = $pagos_contrato
                    ->where('estado', 'pendiente')
                    ->where('tipo_pago', 'cuota')
                    ->sortBy('fecha_pago')
                    ->first();

                // Si hay una siguiente cuota, buscar parcialidades relacionadas
                $parcialidadesRelacionadas = collect();
                if ($siguienteCuota) {
                    // Buscar parcialidades que mencionen el folio de la cuota en observaciones
                    $parcialidadesRelacionadas = $pagos_contrato
                        ->where('tipo_pago', 'parcialidad')
                        ->where('estado', 'hecho')
                        ->filter(function($pago) use ($siguienteCuota) {
                            $observaciones = strtolower($pago->observaciones ?? '');
                            $folioCuota = $siguienteCuota->id;
                            return strpos($observaciones, "cuota $folioCuota") !== false || 
                                   strpos($observaciones, "folio $folioCuota") !== false ||
                                   strpos($observaciones, "#$folioCuota") !== false;
                        });
                }
                @endphp

                @if($siguienteCuota)
                <h6 class="text-muted text-uppercase small fw-bold mb-3">
                    <i class="bi bi-cash-coin me-2"></i>Registrar un nuevo pago
                </h6>
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-calendar-event me-2" style="color: #79481D;"></i>Siguiente cuota
                            </h5>
                            @php
                            $fechaCuota = \Carbon\Carbon::parse($siguienteCuota->fecha_pago);
                            $diasRestantes = now()->diffInDays($fechaCuota, false);
                            $esVencida = $diasRestantes < 0;
                            @endphp
                            @php
                            $esRetrasadoHeader = pagoEstaRetrasado($siguienteCuota->fecha_pago, $siguienteCuota->estado);
                            $enToleranciaHeader = $siguienteCuota->estado == 'pendiente' && 
                                                 $fechaCuota->isPast() && 
                                                 !$esRetrasadoHeader;
                            @endphp
                            @if($esRetrasadoHeader)
                                <span class="badge bg-danger">Retrasada</span>
                            @elseif($enToleranciaHeader)
                                <span class="badge bg-warning text-dark">En gracia</span>
                            @elseif($diasRestantes <= 7)
                                <span class="badge bg-warning text-dark">Próxima a vencer</span>
                            @else
                                <span class="badge bg-success">Al día</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Columna izquierda: Información de la cuota -->
                            <div class="col-8">
                                @php
                                // Usar la misma lógica que en el historial
                                $esRetrasadoCuota = pagoEstaRetrasado($siguienteCuota->fecha_pago, $siguienteCuota->estado);
                                $enToleranciaCuota = $siguienteCuota->estado == 'pendiente' && 
                                                    $fechaCuota->isPast() && 
                                                    !$esRetrasadoCuota;
                                $diasGraciaRestantesCuota = diasDeGraciaRestantes($siguienteCuota->fecha_pago, $siguienteCuota->estado);
                                
                                // Determinar color del borde según el estado
                                $colorBordeCuota = 'border-light';
                                $colorBadgeCuota = 'warning text-dark';
                                $textoBadge = 'Pendiente';
                                
                                if ($esRetrasadoCuota) {
                                    $colorBordeCuota = 'border-danger';
                                    $colorBadgeCuota = 'danger';
                                    $textoBadge = 'Retrasado';
                                } elseif ($enToleranciaCuota) {
                                    $colorBordeCuota = 'border-warning';
                                    $colorBadgeCuota = 'warning text-dark';
                                    $textoBadge = $diasGraciaRestantesCuota > 0 ? 'En gracia por ' . $diasGraciaRestantesCuota . ' días más' : 'Último día de gracia';
                                } elseif ($diasRestantes <= 7 && $diasRestantes > 0) {
                                    $colorBordeCuota = 'border-warning';
                                    $colorBadgeCuota = 'warning text-dark';
                                    $textoBadge = 'Próxima a vencer';
                                } elseif ($diasRestantes > 7) {
                                    $colorBordeCuota = 'border-info';
                                    $colorBadgeCuota = 'info';
                                    $textoBadge = 'Al día';
                                }
                                
                                // Formatear fecha como en el historial
                                $fechaCuotaFormateada = \Carbon\Carbon::parse($siguienteCuota->fecha_pago)->locale('es');
                                $diaCuota = ucfirst($fechaCuotaFormateada->dayName);
                                $fechaCuotaTexto = $fechaCuotaFormateada->format('d') . ' de ' . ucfirst($fechaCuotaFormateada->monthName) . ' de ' . $fechaCuotaFormateada->format('Y');
                                @endphp

                                <!-- Tarjeta de información de cuota con el mismo estilo del historial -->
                                <div class="card payment-card border-start border-3 {{ $colorBordeCuota }}">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <span class="text-{{ str_replace(['text-dark', ' text-dark'], '', $colorBadgeCuota) }}" style="font-size: 1.8em;">
                                                    Cuota {{ $siguienteCuota->numero_cuota ?? 'N/A' }} de {{ $contrato->numero_cuotas }}
                                                </span>
                                                <br>
                                                <span class="badge bg-{{ $colorBadgeCuota }}">
                                                    {{ $textoBadge }}
                                                </span>
                                                <span class="badge bg-secondary">
                                                    Folio #{{ $siguienteCuota->id }}
                                                </span>
                                            </div>
                                            <div class="text-end">
                                                @php
                                                $montoParcialidades = $parcialidadesRelacionadas->sum('monto');
                                                $montoRestante = $siguienteCuota->monto_pendiente;
                                                @endphp

                                                @if($montoParcialidades > 0)
                                                    <div class="text-end">
                                                        <small class="text-muted d-block">Restante</small>
                                                        <p class="fw-bold mb-0 mt-0 text-warning" style="font-size: 1.8em;">${{ number_format($montoRestante, 2) }}</p>
                                                    </div>
                                                @else
                                                    <p class="fw-bold mb-0 mt-1" style="font-size: 1.8em;">${{ number_format($siguienteCuota->monto, 2) }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <p class="mb-1 text-muted">
                                            {{ $diaCuota }}, {{ $fechaCuotaTexto }}
                                        </p>
                                        
                                        <!-- Información adicional del estado -->
                                        @if($esVencida)
                                            <div class="mt-2 p-2 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded">
                                                <small class="text-danger">
                                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                                    @if(abs(intval($diasRestantes)) > 0)
                                                        Vencida hace {{ abs(intval($diasRestantes)) }} día{{ abs(intval($diasRestantes)) != 1 ? 's' : '' }}
                                                    @else
                                                        Vencida hoy
                                                    @endif
                                                </small>
                                            </div>
                                        @elseif($diasRestantes <= 7 && $diasRestantes > 0)
                                            <div class="mt-2 p-2 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded">
                                                <small class="text-warning">
                                                    <i class="bi bi-clock-fill me-1"></i>
                                                    @if($diasRestantes == 1)
                                                        Vence mañana
                                                    @elseif($diasRestantes > 1)
                                                        Vence en {{ intval($diasRestantes) }} día{{ intval($diasRestantes) != 1 ? 's' : '' }}
                                                    @else
                                                        Vence hoy
                                                    @endif
                                                </small>
                                            </div>
                                        @elseif($diasRestantes > 7)
                                            <div class="mt-2 p-2 bg-info bg-opacity-10 border border-info border-opacity-25 rounded">
                                                <small class="text-info">
                                                    <i class="bi bi-calendar-check-fill me-1"></i>
                                                    @if($diasRestantes == 1)
                                                        Falta 1 día para el pago
                                                    @else
                                                        Faltan {{ intval($diasRestantes) }} días para el pago
                                                    @endif
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Columna derecha: Progreso y acciones -->
                            <div class="col-4">
                                @php
                                $montoParcialidades = $parcialidadesRelacionadas->sum('monto');
                                $montoRestante = $siguienteCuota->monto_pendiente;
                                
                                // Calcular cuota regular del contrato
                                $montoInicial = $contrato->monto_inicial ?? 0;
                                $montoBonificacion = $contrato->monto_bonificacion ?? 0;
                                $montoFinanciado = $contrato->monto_total - $montoInicial - $montoBonificacion;
                                $cuotaRegular = $contrato->numero_cuotas > 0 ? $montoFinanciado / $contrato->numero_cuotas : 0;
                                
                                // Calcular porcentaje que representa el saldo restante respecto a la cuota regular
                                $porcentajeSaldoVsCuota = $cuotaRegular > 0 ? ($montoRestante / $cuotaRegular) * 100 : 0;
                                $porcentajePagadoVsCuota = $cuotaRegular > 0 ? ($montoParcialidades / $cuotaRegular) * 100 : 0;
                                @endphp
                                
                                <div class="text-center h-100 d-flex flex-column justify-content-between">
                                    <!-- Gráfico de progreso -->
                                    <div class="mb-3">


                                        <h6 class="fw-bold mb-2" style="font-size: 0.9em;">Progreso de Pago de Cuota</h6>
                                        
                                        <!-- Información de montos -->
                                        <div class="row text-center mb-2">
                                            <div class="col-6">
                                                <small class="text-muted d-block">Pagado</small>
                                                <small class="fw-bold text-success" style="font-size: 1.2em;">${{ number_format($montoParcialidades, 2) }}</small>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted d-block">Restante</small>
                                                <small class="fw-bold text-secondary" style="font-size: 1.2em;">${{ number_format(max(0, $montoRestante), 2) }}</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Botones de acción -->
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('pagos.show', $siguienteCuota->id) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye me-1"></i>Ver detalles
                                        </a>
                                        <a href="{{ route('pagos.create', ['contrato_id' => $contrato->id]) }}" class="btn btn-success btn-sm">
                                            <i class="bi bi-cash-coin me-1"></i>Registrar pago
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if(Auth::check() && Auth::user()->role === 'admin')
                <!-- Historial de pagos -->
                <div class="card">
                    <div class="accordion accordion-flush" id="accordionHistorialPagos">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingHistorialPagos">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHistorialPagos" aria-expanded="false" aria-controls="collapseHistorialPagos">
                                    <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                        <div>
                                            <h5 class="card-title mb-0"><i class="bi bi-credit-card me-2" style="color: #79481D;"></i>Historial de Pagos</h5>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <span class="badge bg-success">{{ $pagos_contrato->where('estado', 'hecho')->count() }} pagos</span>
                                            <span class="badge bg-warning text-dark">{{ $pagos_contrato->where('estado', 'pendiente')->count() }} cuotas pendientes</span>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapseHistorialPagos" class="accordion-collapse collapse" aria-labelledby="headingHistorialPagos" data-bs-parent="#accordionHistorialPagos">
                                <div class="accordion-body">
                                    <div class="timeline">
                                        @forelse($pagos_contrato->sortBy('fecha_pago') as $pago)
                                        <div class="timeline-item mb-4">
                                @php
                                $fechaPago = \Carbon\Carbon::parse($pago->fecha_pago)->locale('es');
                                $diaSemana = ucfirst($fechaPago->dayName);
                                $mesNombre = ucfirst($fechaPago->monthName);
                                $fechaFormateada = $fechaPago->format('d') . ' de ' . $mesNombre . ' de ' . $fechaPago->format('Y');
                                $esRetrasado = pagoEstaRetrasado($pago->fecha_pago, $pago->estado);
                                $esPagoEspecial = in_array(strtolower($pago->tipo_pago ?? ''), ['inicial', 'bonificación']);
                                $esParcialidad = strtolower($pago->tipo_pago ?? '') == 'parcialidad';
                                
                                // Determinar si está en período de tolerancia (vencido pero dentro del margen)
                                $enTolerancia = $pago->estado == 'pendiente' && 
                                               $fechaPago->isPast() && 
                                               !$esRetrasado;
                                
                                // Calcular días de gracia restantes
                                $diasGraciaRestantes = diasDeGraciaRestantes($pago->fecha_pago, $pago->estado);
                                
                                // Determinar color del borde
                                $colorBorde = 'border-light';
                                if ($esPagoEspecial && $pago->estado == 'hecho') {
                                    $colorBorde = 'border-info';
                                } elseif ($esParcialidad && $pago->estado == 'hecho') {
                                    $colorBorde = 'border-success-light';
                                } elseif ($pago->estado == 'hecho') {
                                    $colorBorde = 'border-success';
                                } elseif ($esRetrasado) {
                                    $colorBorde = 'border-danger';
                                } elseif ($enTolerancia) {
                                    $colorBorde = 'border-warning';
                                } elseif ($pago->estado == 'pendiente') {
                                    $colorBorde = 'border-secondary';
                                }
                                
                                // Determinar color del badge
                                $colorBadge = 'secondary';
                                if ($esPagoEspecial && $pago->estado == 'hecho') {
                                    $colorBadge = 'info';
                                } elseif ($esParcialidad && $pago->estado == 'hecho') {
                                    $colorBadge = 'success-light';
                                } elseif ($pago->estado == 'hecho') {
                                    $colorBadge = 'success';
                                } elseif ($esRetrasado) {
                                    $colorBadge = 'danger';
                                } elseif ($enTolerancia) {
                                    $colorBadge = 'warning text-dark';
                                } elseif ($pago->estado == 'pendiente') {
                                    $colorBadge = 'secondary';
                                }
                                @endphp
                                
                                @if($esParcialidad)
                                    <!-- Acordeón para pagos de tipo parcialidad -->
                                    <div class="accordion accordion-flush" id="accordion-pago-{{ $pago->id }}">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="heading-pago-{{ $pago->id }}">
                                                <button class="accordion-button collapsed payment-card border-start border-3 {{ $colorBorde }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-pago-{{ $pago->id }}" aria-expanded="false" aria-controls="collapse-pago-{{ $pago->id }}">
                                                    <div class="d-flex justify-content-between w-100 me-3">
                                                        <div>
                                                            @php
                                                            // Obtener número de cuota directamente del pago padre
                                                            $numeroCuotaPadre = null;
                                                            if ($pago->pago_padre_id) {
                                                                $pagoPadre = $pagos_contrato->where('id', $pago->pago_padre_id)->first();
                                                                $numeroCuotaPadre = $pagoPadre ? $pagoPadre->numero_cuota : null;
                                                            }
                                                            
                                                            $tituloParcialidad = $numeroCuotaPadre 
                                                                ? "Parcialidad de la Cuota $numeroCuotaPadre"
                                                                : 'Parcialidad';
                                                            @endphp
                                                            <span class="text-{{ str_replace(['text-dark', ' text-dark'], '', $colorBadge) }}" style="font-size: 1.8em;">
                                                                {{ $tituloParcialidad }}
                                                            </span>
                                                            <br>
                                                            <span class="badge bg-{{ $colorBadge }}">
                                                                {{ $esRetrasado ? 'Retrasado' : ($enTolerancia ? ($diasGraciaRestantes > 0 ? 'En gracia por ' . $diasGraciaRestantes . ' días más' : 'Último día de gracia') : ucfirst($pago->estado)) }}
                                                            </span>
                                                            <span class="badge bg-secondary">
                                                                Folio #{{ $pago->id }}
                                                            </span>
                                                            <p class="mb-1 text-muted">
                                                                {{ $diaSemana }}, {{ $fechaFormateada }}
                                                            </p>
                                                        </div>
                                                        <div class="text-end">
                                                            <p class="fw-bold mb-0 mt-1" style="font-size: 1.8em;">${{ number_format($pago->monto, 2) }}</p>
                                                        </div>
                                                    </div>
                                                </button>
                                            </h2>
                                            <div id="collapse-pago-{{ $pago->id }}" class="accordion-collapse collapse" aria-labelledby="heading-pago-{{ $pago->id }}" data-bs-parent="#accordion-pago-{{ $pago->id }}">
                                                <div class="accordion-body">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <p class="mb-0 mt-2">
                                                                <div class="d-flex align-items-center gap-1">
                                                                    <i class="bi bi-credit-card text-primary"></i>
                                                                    <span class="text-muted small">Método: {{ $pago->metodo_pago ?? 'N/A' }}</span>
                                                                </div>
                                                                <div class="d-flex align-items-center gap-1 mt-1">
                                                                    <i class="bi bi-tag text-success"></i>
                                                                    <span class="text-muted small">Tipo: {{ ucfirst($pago->tipo_pago ?? 'N/A') }}</span>
                                                                </div>
                                                            </p>
                                                        </div>
                                                        <div>
                                                            <a href="{{ route('pagos.show', $pago->id) }}" class="btn btn-outline-secondary btn-sm">
                                                                <i class="bi bi-receipt me-1"></i>Recibo de parcialidad
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <!-- Tarjeta normal para otros tipos de pago -->
                                    <a href="{{ route('pagos.show', $pago->id) }}" class="text-decoration-none">
                                        <div class="card payment-card border-start border-3 {{ $colorBorde }}">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        @if(in_array(strtolower($pago->tipo_pago ?? ''), ['inicial', 'bonificación']))
                                                            <span class="text-{{ str_replace(['text-dark', ' text-dark'], '', $colorBadge) }}" style="font-size: 1.8em;">
                                                                {{ ucfirst($pago->tipo_pago) }}
                                                            </span>
                                                        @else
                                                            <span class="text-{{ str_replace(['text-dark', ' text-dark'], '', $colorBadge) }}" style="font-size: 1.8em;">
                                                                Cuota {{ $pago->numero_cuota }} de {{ $contrato->numero_cuotas }}
                                                            </span>
                                                        @endif
                                                        <br>
                                                        <span class="badge bg-{{ $colorBadge }}">
                                                            {{ $esRetrasado ? 'Retrasado' : ($enTolerancia ? ($diasGraciaRestantes > 0 ? 'En gracia por ' . $diasGraciaRestantes . ' días más' : 'Último día de gracia') : ucfirst($pago->estado)) }}
                                                        </span>
                                                        <span class="badge bg-secondary">
                                                            Folio #{{ $pago->id }}
                                                        </span>
                                                    </div>
                                                    <div class="text-end">
                                                        <p class="fw-bold mb-0 mt-1" style="font-size: 1.8em;">${{ number_format($pago->monto, 2) }}</p>
                                                    </div>
                                                </div>
                                                <p class="mb-0 mt-2">
                                                    <div class="d-flex align-items-center gap-1">
                                                        <i class="bi bi-credit-card text-primary"></i>
                                                        <span class="text-muted small">Método: {{ $pago->metodo_pago ?? 'N/A' }}</span>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-1 mt-1">
                                                        <i class="bi bi-tag text-success"></i>
                                                        <span class="text-muted small">Tipo: {{ ucfirst($pago->tipo_pago ?? 'N/A') }}</span>
                                                    </div>
                                                </p>
                                                <p class="mb-1 text-muted">
                                                    {{ $diaSemana }}, {{ $fechaFormateada }}
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                        @endif
                                    </div>
                                    @empty
                                    <div class="timeline-item">
                                        <div class="card payment-card border-start border-3 border-light">
                                            <div class="card-body">
                                                <p class="mb-0 text-muted">No hay pagos registrados para este contrato.</p>
                                            </div>
                                        </div>
                                    </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            @if(Auth::check() && Auth::user()->role === 'admin')
            <!-- Columna derecha - Resumen y acciones -->
            <div class="col-lg-4">
                <!-- Resumen financiero -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0"><i class="bi bi-graph-up me-2" style="color: #79481D;"></i>Resumen Financiero</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            @php
                            // Calcular el total adeudado de todo el contrato (pagos vencidos/retrasados)
                            $totalAdeudadoContrato = $pagos_contrato
                                ->where('estado', 'pendiente')
                                ->filter(function($pago) {
                                    return pagoEstaRetrasado($pago->fecha_pago, $pago->estado);
                                })
                                ->sum(function($pago) {
                                    return $pago->monto_pendiente ?? $pago->monto;
                                });
                            @endphp
                            <div class="col-6">
                                <h6>Total Adeudado</h6>
                                <h4 class="fw-bold text-danger">${{ number_format($totalAdeudadoContrato, 2) }}</h4>
                            </div>
                            <div class="col-6 text-end">
                                <p class="mb-1">Total a pagar</p>
                                <h4 class="fw-bold">${{ number_format($contrato->monto_total, 2) }}</h4>
                            </div>
                        </div>

                        <div class="progress mb-3 position-relative" style="height: 12px; background-color: #e9ecef; border-radius: 5px; overflow: hidden;">
                            @php
                            // Calcular el monto pagado correctamente evitando conteo duplicado
                            $pagado = calcularMontoPagadoContrato($pagos_contrato);
                            $porcentajePagado = $contrato->monto_total > 0
                                ? min(100, ($pagado / $contrato->monto_total) * 100)
                                : 0;
                            @endphp
                            <div class="progress-bar" role="progressbar" style="width: {{ number_format($porcentajePagado, 2) }}%; background: linear-gradient(90deg, #28a745, #218838); box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.2);" aria-valuenow="{{ number_format($porcentajePagado, 2) }}" aria-valuemin="0" aria-valuemax="100"></div>
                            <span class="position-absolute top-50 start-50 translate-middle text-black fw-bold" style="z-index:2; font-size: 0.9em; ">
                                {{ number_format($porcentajePagado, 1) }}%
                            </span>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <p class="mb-1 text-success">Pagado</p>
                                <h5 class="fw-bold">${{ number_format($pagado, 2) }}</h5>
                            </div>
                            <div class="col-6 text-end">
                                <p class="mb-1 text-warning">Pendiente</p>
                                <h5 class="fw-bold">${{ number_format($contrato->monto_total - $pagado, 2) }}</h5>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-2">
                            <p class="mb-1"><small>Próximo pago:</small></p>
                            @php
                            $proximoPago = $pagos_contrato->where('estado', 'pendiente')
                            ->where('fecha_pago', '>=', now())
                            ->sortBy('fecha_pago')
                            ->first();
                            @endphp
                            @if($proximoPago)
                            <p class="fw-bold mb-0">
                                ${{ number_format($proximoPago->monto, 2) }}
                                <span class="text-muted">
                                    ({{ \Carbon\Carbon::parse($proximoPago->fecha_pago)->format('d') }} de {{ ucfirst(\Carbon\Carbon::parse($proximoPago->fecha_pago)->locale('es')->monthName) }} de {{ \Carbon\Carbon::parse($proximoPago->fecha_pago)->format('Y') }} )
                                </span>
                            </p>
                            @else
                            <p class="fw-bold mb-0 text-muted">No hay pagos pendientes próximos.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Acciones rápidas -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0"><i class="bi bi-lightning me-2" style="color: #79481D;"></i>Acciones Rápidas</h5>
                    </div>
                    <div class="card-body">

                        <!-- <div class="row g-2 mb-3">
                            <div class="col-12">
                                <button type="button" class="btn btn-outline-secondary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#estadoCuentaModal">
                                    <i class="bi bi-file-text me-1"></i>Estado de cuenta
                                </button>
                            </div>
                        </div> -->

                        <!-- Primera fila: Comisiones y WhatsApp -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <a href="{{ route('contratos.comisiones', $contrato->id) }}" class="btn btn-outline-primary btn-sm w-100">
                                    <i class="bi bi-list-ul me-1"></i>Comisiones
                                </a>
                            </div>
                            <div class="col-6">
                                @php
                                    $mensajePersonalizado = generarMensajeWhatsApp($contrato, $proximoPago ?? null);
                                @endphp
                                <a href="https://wa.me/{{ preg_replace('/\D/', '', $contrato->cliente->telefono) }}?text={{ urlencode($mensajePersonalizado) }}" target="_blank" class="btn btn-outline-success btn-sm w-100">
                                    <i class="bi bi-whatsapp me-1"></i>Recordatorio
                                </a>
                            </div>
                        </div>

                        <!-- Segunda fila: Acciones de estado dinámicas -->
                        <div class="d-grid gap-2">
                            @if($contrato->estado == 'activo')
                            <div class="row g-2">
                                <div class="col-4">
                                    <button class="btn btn-outline-warning btn-sm w-100" data-bs-toggle="modal" data-bs-target="#suspenderModal" title="Suspender contrato">
                                        <i class="bi bi-pause-circle me-1"></i>Suspender
                                    </button>
                                </div>
                                <div class="col-4">
                                    <button class="btn btn-outline-success btn-sm w-100" data-bs-toggle="modal" data-bs-target="#finalizarModal" title="Finalizar contrato">
                                        <i class="bi bi-check-circle me-1"></i>Finalizar
                                    </button>
                                </div>
                                <div class="col-4">
                                    <button class="btn btn-outline-danger btn-sm w-100" data-bs-toggle="modal" data-bs-target="#cancelarModal" title="Cancelar contrato">
                                        <i class="bi bi-x-circle me-1"></i>Cancelar
                                    </button>
                                </div>
                            </div>
                            @elseif($contrato->estado == 'suspendido')
                            <div class="row g-2">
                                <div class="col-6">
                                    <button class="btn btn-outline-success btn-sm w-100" data-bs-toggle="modal" data-bs-target="#reactivarModal" title="Reactivar contrato">
                                        <i class="bi bi-play-circle me-1"></i>Reactivar
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button class="btn btn-outline-danger btn-sm w-100" data-bs-toggle="modal" data-bs-target="#cancelarModal" title="Cancelar contrato">
                                        <i class="bi bi-x-circle me-1"></i>Cancelar
                                    </button>
                                </div>
                            </div>
                            @elseif($contrato->estado == 'cancelado')
                            <div class="row g-2">
                                <div class="col-12">
                                    <button class="btn btn-outline-info btn-sm w-100" data-bs-toggle="modal" data-bs-target="#reactivarModal" title="Reactivar contrato">
                                        <i class="bi bi-arrow-clockwise me-1"></i>Reactivar contrato
                                    </button>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Previsualización del documento PDF -->
                @if($contrato->documento && $contrato->documento !== 'No')
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0"><i class="bi bi-file-earmark-pdf me-2" style="color: #79481D;"></i>Documento del Contrato</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <!-- Información adicional del documento -->
                            <div class="bg-light rounded">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                                        <small class="fw-bold">{{ Str::limit(basename($contrato->documento), 30) }}</small>
                                    </div>
                                    <div>
                                        <small class="text-muted">{{ $contrato->updated_at->format('d/m/Y') }}</small>
                                    </div>
                                </div>
                            </div>
                            <!-- Iframe para mostrar el PDF -->
                            <div class="pdf-preview-container my-3" style="height: 500px; border: 1px solid #dee2e6; border-radius: 0.375rem; overflow: hidden; position: relative; cursor: pointer;" onclick="openPDFInNewWindow('{{ asset('storage/' . $contrato->documento) }}')">
                                <iframe
                                    src="{{ asset('storage/' . $contrato->documento) }}#toolbar=0&navpanes=0&scrollbar=0&statusbar=0&messages=0&scrollbar=0&view=FitH"
                                    width="100%"
                                    height="100%"
                                    type="application/pdf"
                                    title="Documento del Contrato #{{ $contrato->id }}"
                                    frameborder="0"
                                    style="pointer-events: none;">
                                    <p class="text-muted p-3">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        Tu navegador no puede mostrar archivos PDF.
                                        <a href="{{ asset('storage/' . $contrato->documento) }}" target="_blank" class="text-decoration-none">
                                            Haz clic aquí para descargar el documento
                                        </a>
                                    </p>
                                </iframe>
                                <!-- Overlay invisible para capturar clicks -->
                                <div class="position-absolute top-0 start-0 w-100 h-100" style="background: transparent; z-index: 1;" title="Hacer clic para abrir en pantalla completa"></div>
                            </div>

                            <!-- Botones de acción para el PDF -->
                            <div class="row g-2">
                                <!-- Primera fila -->
                                <div class="col-12">
                                    <button type="button"
                                        class="btn btn-outline-warning btn-sm w-100"
                                        onclick="document.getElementById('reemplazarDocumentoInput').click()"
                                        title="Reemplazar documento">
                                        <i class="bi bi-arrow-repeat me-1"></i>Reemplazar Documento
                                    </button>
                                </div>
                                <!-- Segunda fila -->
                                <div class="col-6">
                                    <a href="{{ asset('storage/' . $contrato->documento) }}"
                                        download
                                        class="btn btn-outline-success btn-sm w-100 text-center"
                                        title="Descargar PDF">
                                        <i class="bi bi-download me-1"></i>Descargar
                                    </a>
                                </div>
                                <div class="col-6">
                                    <div class="dropdown">
                                        <button type="button"
                                            class="btn btn-outline-info btn-sm w-100 dropdown-toggle"
                                            data-bs-toggle="dropdown"
                                            aria-expanded="false"
                                            title="Opciones de impresión">
                                            <i class="bi bi-printer me-1"></i>Imprimir
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="#" 
                                                   onclick="printPDF('{{ asset('storage/' . $contrato->documento) }}'); return false;">
                                                    <i class="bi bi-printer me-2"></i>Imprimir directamente
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" 
                                                   onclick="printPDFNewWindow('{{ asset('storage/' . $contrato->documento) }}'); return false;">
                                                    <i class="bi bi-window me-2"></i>Abrir e imprimir en nueva ventana
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-muted" href="{{ asset('storage/' . $contrato->documento) }}" target="_blank">
                                                    <i class="bi bi-box-arrow-up-right me-2"></i>Abrir en nueva pestaña
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Formulario oculto para reemplazar documento -->
                            <form id="reemplazarDocumentoForm" action="{{ route('contratos.updateDocumento', $contrato->id) }}" method="POST" enctype="multipart/form-data" style="display: none;">
                                @csrf
                                @method('PATCH')
                                <input type="file"
                                    id="reemplazarDocumentoInput"
                                    name="documento"
                                    accept=".pdf"
                                    onchange="handleDocumentReplace(this)">
                            </form>
                        </div>
                    </div>
                </div>
                @else
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0"><i class="bi bi-file-earmark-pdf me-2" style="color: #79481D;"></i>Documento del Contrato</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning text-center mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            No hay documento PDF asociado a este contrato.
                        </div>
                        <div class="mt-3 text-center">
                            <button type="button"
                                class="btn btn-outline-primary btn-sm"
                                onclick="document.getElementById('reemplazarDocumentoInput').click()">
                                <i class="bi bi-upload me-1"></i>Subir Documento PDF
                            </button>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Formulario oculto para subir/reemplazar documento (único para ambos casos) -->
                <form id="reemplazarDocumentoForm" action="{{ route('contratos.updateDocumento', $contrato->id) }}" method="POST" enctype="multipart/form-data" style="display: none;">
                    @csrf
                    @method('PATCH')
                    <input type="file"
                        id="reemplazarDocumentoInput"
                        name="documento"
                        accept=".pdf"
                        onchange="handleDocumentReplace(this)">
                </form>

                <!-- Observaciones del contrato -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0"><i class="bi bi-chat-left-text me-2" style="color: #79481D;"></i>Observaciones</h5>
                    </div>
                    <div class="card-body">
                        <form id="observacionesForm">
                            @csrf
                            @method('PATCH')
                            <div class="mb-3">
                                <textarea class="form-control" id="observacionesTextarea" name="observaciones" rows="4"
                                    placeholder="Escriba aquí las observaciones del contrato...">{{ $contrato->observaciones }}</textarea>
                                <div class="form-text" id="caracteresRestantes">Máximo 1000 caracteres</div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success btn-sm" id="guardarObservacionesBtn">
                                    <i class="bi bi-check me-1"></i>Guardar Cambios
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="resetObservacionesBtn">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Deshacer Cambios
                                </button>
                            </div>
                        </form>
                        <div id="observacionesLoading" style="display: none;">
                            <div class="text-center">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Guardando...</span>
                                </div>
                                <span class="ms-2 text-muted">Guardando...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta para editar contrato -->
                <div class="card mt-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0"><i class="bi bi-pencil-square me-2" style="color: #79481D;"></i>Editar Contrato</h5>
                    </div>
                    <div class="card-body text-center">
                        <p class="mb-3 text-muted">¿Necesitas modificar los datos de este contrato?</p>
                        <a href="{{ route('contratos.edit', $contrato->id) }}" class="btn btn-outline-primary w-100">
                            <i class="bi bi-pencil-square me-1"></i>Ir a edición de contrato
                        </a>
                    </div>
                </div>
            </div>
            @endif                                         
        </div>
    </div>
    </div>
</section>

<!-- Formularios ocultos para las acciones -->
<form id="suspender-contrato-form" action="{{ route('contratos.suspender', $contrato->id) }}" method="POST" style="display: none;">
    @csrf
    @method('PATCH')
</form>

<form id="finalizar-contrato-form" action="{{ route('contratos.finalizar', $contrato->id) }}" method="POST" style="display: none;">
    @csrf
    @method('PATCH')
</form>

<form id="cancelar-contrato-form" action="{{ route('contratos.cancel', $contrato->id) }}" method="POST" style="display: none;">
    @csrf
    @method('PATCH')
</form>

<form id="reactivar-contrato-form" action="{{ route('contratos.reactivar', $contrato->id) }}" method="POST" style="display: none;">
    @csrf
    @method('PATCH')
</form>

<!-- Modal para Suspender Contrato -->
<div class="modal fade" id="suspenderModal" tabindex="-1" aria-labelledby="suspenderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="suspenderModalLabel">
                    <i class="bi bi-pause-circle me-2"></i>Suspender Contrato
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                </div>
                <h6 class="text-center mb-3">¿Estás seguro de que deseas suspender este contrato?</h6>
                <div class="alert alert-warning">
                    <strong>Contrato:</strong> {{ $contrato->paquete->nombre }}#{{ $contrato->id }}<br>
                    <strong>Cliente:</strong> {{ $contrato->cliente->nombre }} {{ $contrato->cliente->apellido }}<br>
                    <strong>Estado actual:</strong> <span class="badge bg-success">{{ strtoupper($contrato->estado) }}</span>
                </div>
                <p class="text-muted small">
                    <i class="bi bi-info-circle me-1"></i>
                    Al suspender el contrato, se pausarán los pagos programados y el cliente no podrá acceder a los servicios hasta que se reactive.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-warning" onclick="document.getElementById('suspender-contrato-form').submit();">
                    <i class="bi bi-pause-circle me-1"></i>Suspender Contrato
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Finalizar Contrato -->
<div class="modal fade" id="finalizarModal" tabindex="-1" aria-labelledby="finalizarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="finalizarModalLabel">
                    <i class="bi bi-check-circle me-2"></i>Finalizar Contrato
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                </div>
                <h6 class="text-center mb-3">¿Estás seguro de que deseas finalizar este contrato?</h6>
                <div class="alert alert-info">
                    <strong>Contrato:</strong> {{ $contrato->paquete->nombre }}#{{ $contrato->id }}<br>
                    <strong>Cliente:</strong> {{ $contrato->cliente->nombre }} {{ $contrato->cliente->apellido }}<br>
                    <strong>Estado actual:</strong> <span class="badge bg-success">{{ strtoupper($contrato->estado) }}</span>
                </div>
                <p class="text-muted small">
                    <i class="bi bi-info-circle me-1"></i>
                    Al finalizar el contrato, se marcará como completado exitosamente. Esta acción indica que el contrato se ha cumplido satisfactoriamente.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-success" onclick="document.getElementById('finalizar-contrato-form').submit();">
                    <i class="bi bi-check-circle me-1"></i>Finalizar Contrato
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Cancelar Contrato -->
<div class="modal fade" id="cancelarModal" tabindex="-1" aria-labelledby="cancelarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="cancelarModalLabel">
                    <i class="bi bi-x-circle me-2"></i>Cancelar Contrato
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                </div>
                <h6 class="text-center mb-3">¿Estás seguro de que deseas cancelar este contrato?</h6>
                <div class="alert alert-danger">
                    <strong>Contrato:</strong> {{ $contrato->paquete->nombre }}#{{ $contrato->id }}<br>
                    <strong>Cliente:</strong> {{ $contrato->cliente->nombre }} {{ $contrato->cliente->apellido }}<br>
                    <strong>Estado actual:</strong> <span class="badge bg-{{ $contrato->estado == 'activo' ? 'success' : ($contrato->estado == 'suspendido' ? 'warning' : 'secondary') }}">{{ strtoupper($contrato->estado) }}</span>
                </div>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <strong>¡Atención!</strong> Esta acción es irreversible y tendrá las siguientes consecuencias:
                    <ul class="mb-0 mt-2">
                        <li>Se cancelarán todos los pagos pendientes</li>
                        <li>El cliente perderá acceso a los servicios</li>
                        <li>Se podrá reactivar más tarde si es necesario</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-arrow-left me-1"></i>No, mantener contrato
                </button>
                <button type="button" class="btn btn-danger" onclick="document.getElementById('cancelar-contrato-form').submit();">
                    <i class="bi bi-x-circle me-1"></i>Sí, cancelar contrato
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Reactivar Contrato -->
<div class="modal fade" id="reactivarModal" tabindex="-1" aria-labelledby="reactivarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="reactivarModalLabel">
                    <i class="bi bi-play-circle me-2"></i>Reactivar Contrato
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="bi bi-arrow-clockwise text-info" style="font-size: 3rem;"></i>
                </div>
                <h6 class="text-center mb-3">¿Estás seguro de que deseas reactivar este contrato?</h6>
                <div class="alert alert-info">
                    <strong>Contrato:</strong> {{ $contrato->paquete->nombre }}#{{ $contrato->id }}<br>
                    <strong>Cliente:</strong> {{ $contrato->cliente->nombre }} {{ $contrato->cliente->apellido }}<br>
                    <strong>Estado actual:</strong> <span class="badge bg-{{ $contrato->estado == 'suspendido' ? 'warning' : ($contrato->estado == 'cancelado' ? 'danger' : 'secondary') }}">{{ strtoupper($contrato->estado) }}</span>
                </div>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-1"></i>
                    <strong>Al reactivar el contrato:</strong>
                    <ul class="mb-0 mt-2">
                        <li>El contrato volverá al estado activo</li>
                        <li>Se reanudarán los pagos programados</li>
                        <li>El cliente recuperará acceso a los servicios</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-info text-white" onclick="document.getElementById('reactivar-contrato-form').submit();">
                    <i class="bi bi-play-circle me-1"></i>Reactivar Contrato
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .hover-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    .client-avatar {
        position: relative;
    }

    .client-stat {
        transition: all 0.2s ease;
    }

    .hover-card:hover .client-stat {
        transform: scale(1.05);
    }

    .payment-card {
        transition: all 0.3s ease;
    }

    .payment-card:hover {
        transform: translateX(5px);
        box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1) !important;
    }

    .status-badge {
        font-size: 0.875rem;
        padding: 0.5rem 1rem;
    }

    /* Estilos para información de estado de pagos en el header */
    .header-actions .payment-status-info {
        font-size: 0.85rem;
        min-width: 280px;
        max-width: 350px;
    }

    .header-actions .payment-status-info .alert {
        border-radius: 10px !important;
        margin-bottom: 0.5rem !important;
        padding: 0.75rem !important;
        border: none !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        backdrop-filter: blur(10px);
    }

    .header-actions .payment-status-info .alert-danger {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%) !important;
        color: #991b1b !important;
        border-left: 4px solid #dc3545 !important;
    }

    .header-actions .payment-status-info .alert-success {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%) !important;
        color: #065f46 !important;
        border-left: 4px solid #10b981 !important;
    }

    .header-actions .payment-status-info .fw-bold {
        font-size: 0.9rem;
        margin-bottom: 0.25rem;
    }

    .header-actions .payment-status-info small {
        font-size: 0.75rem;
        line-height: 1.4;
    }

    .header-actions .payment-status-info i {
        width: 16px;
        font-size: 0.85rem;
    }

    .timeline-item {
        position: relative;
    }

    .timeline-item:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 1.5rem;
        top: 100%;
        width: 2px;
        height: 20px;
        background: #e9ecef;
    }

    /* Estilos para el gráfico circular de progreso */
    .progress-ring {
        transform: rotate(-90deg);
    }

    .progress-ring circle {
        transition: stroke-dashoffset 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Animación suave al cargar */
    @keyframes progressRingAnimation {
        from {
            stroke-dashoffset: 220;
            /* circumference para radio 35 */
        }
    }

    .progress-ring circle:nth-child(2) {
        animation: progressRingAnimation 1.5s ease-out;
    }

    /* Estilos para la previsualización del PDF */
    .pdf-preview-container {
        background: #f8f9fa;
        position: relative;
        transition: all 0.2s ease;
    }

    .pdf-preview-container:hover {
        box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1);
        transform: translateY(-1px);
    }

    .pdf-preview-container iframe {
        border: none;
        background: white;
        pointer-events: none;
        /* Deshabilita la interacción */
    }

    .pdf-preview-container::before {
        content: '';
        position: absolute;
        top: 10px;
        right: 10px;
        width: 20px;
        height: 20px;
        background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%23dc3545"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/></svg>') no-repeat center;
        background-size: contain;
        z-index: 2;
        pointer-events: none;
    }

    .pdf-preview-container::after {
        content: 'Hacer clic para abrir en pantalla completa';
        position: absolute;
        bottom: 10px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        opacity: 0;
        transition: opacity 0.2s ease;
        z-index: 2;
        pointer-events: none;
    }

    .pdf-preview-container:hover::after {
        opacity: 1;
    }

    /* Responsive para el PDF */
    @media (max-width: 768px) {
        .pdf-preview-container {
            height: 300px;
        }
    }

    /* Estilos para los modales de confirmación */
    .modal-content {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        border-radius: 15px 15px 0 0;
        border-bottom: 2px solid rgba(255, 255, 255, 0.2);
    }

    .modal-footer {
        border-top: 1px solid #dee2e6;
        border-radius: 0 0 15px 15px;
    }

    .modal-backdrop {
        background-color: rgba(0, 0, 0, 0.6);
    }

    /* Animación para los iconos de los modales */
    .modal-body i[style*="font-size: 3rem"] {
        animation: modalIconPulse 1.5s ease-in-out infinite;
    }

    @keyframes modalIconPulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.1);
        }
    }

    /* Hover effects para botones de modal */
    .modal-footer .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    /* Estilos para parcialidades con verde claro */
    .border-success-light {
        border-color: #35c08b !important; /* Verde claro personalizado */
    }

    .bg-success-light {
        background-color: #35c08b !important; /* Verde claro personalizado */
        color: #ffffff !important; /* Texto blanco para mejor contraste */
    }

    /* Estilos para el dropdown de impresión */
    .dropdown-menu {
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        animation: dropdownFadeIn 0.15s ease-out;
    }

    @keyframes dropdownFadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .dropdown-item {
        padding: 0.5rem 1rem;
        transition: all 0.15s ease-in-out;
    }

    .dropdown-item:hover {
        background-color: #f8f9fa;
        transform: translateX(2px);
    }

    .dropdown-item.text-muted:hover {
        background-color: #e9ecef;
        color: #495057 !important;
    }

    .dropdown-item i {
        width: 20px;
        text-align: center;
    }

    /* Estilos para instrucciones de impresión */
    .print-instructions {
        border-left: 4px solid #0dcaf0;
        background-color: #cff4fc;
        border-color: #b6effb;
    }

    .print-instructions kbd {
        background-color: #212529;
        color: #fff;
        padding: 0.2rem 0.4rem;
        border-radius: 0.25rem;
        font-size: 0.875em;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const resetBtn = document.getElementById('resetObservacionesBtn');
        const guardarBtn = document.getElementById('guardarObservacionesBtn');
        const observacionesLoading = document.getElementById('observacionesLoading');
        const form = document.getElementById('observacionesForm');
        const textarea = document.getElementById('observacionesTextarea');
        const caracteresRestantes = document.getElementById('caracteresRestantes');

        let originalValue = textarea.value;

        // Función para mostrar estado de carga
        function showLoading() {
            form.style.display = 'none';
            observacionesLoading.style.display = 'block';
        }

        // Función para ocultar estado de carga
        function hideLoading() {
            form.style.display = 'block';
            observacionesLoading.style.display = 'none';
        }

        // Función para mostrar mensaje
        function showMessage(message, type = 'success') {
            // Remover mensajes anteriores
            const existingAlerts = form.querySelectorAll('.alert');
            existingAlerts.forEach(alert => alert.remove());

            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show mt-2`;
            const icon = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle';
            alertDiv.innerHTML = `
                    <i class="bi ${icon} me-2"></i>${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
            form.appendChild(alertDiv);

            // Remover alerta después de unos segundos
            const timeout = type === 'success' ? 3000 : 5000;
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, timeout);
        }

        // Evento click en botón restaurar
        resetBtn.addEventListener('click', function() {
            textarea.value = originalValue;
            updateCharacterCounter();
        });

        // Función para actualizar contador de caracteres
        function updateCharacterCounter() {
            const remaining = 1000 - textarea.value.length;
            if (remaining < 0) {
                caracteresRestantes.textContent = `Excedido por ${Math.abs(remaining)} caracteres`;
                caracteresRestantes.classList.add('text-danger');
                caracteresRestantes.classList.remove('text-muted');
                guardarBtn.disabled = true;
            } else {
                caracteresRestantes.textContent = `${remaining} caracteres restantes`;
                caracteresRestantes.classList.remove('text-danger');
                caracteresRestantes.classList.add('text-muted');
                guardarBtn.disabled = false;
            }
        }

        // Contador de caracteres en tiempo real
        textarea.addEventListener('input', updateCharacterCounter);

        // Inicializar contador de caracteres
        updateCharacterCounter();

        // Evento submit del formulario
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData();
            formData.append('_token', document.querySelector('input[name="_token"]').value);
            formData.append('_method', 'PATCH');
            formData.append('observaciones', textarea.value);

            showLoading();

            fetch('{{ route("contratos.updateObservaciones", $contrato->id) }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    hideLoading();

                    if (data.success) {
                        showMessage(data.message, 'success');
                        // Actualizar valor original
                        originalValue = textarea.value;
                    } else {
                        showMessage(data.message || 'Error al actualizar las observaciones', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    hideLoading();
                    showMessage('Error al actualizar las observaciones. Por favor, inténtelo de nuevo.', 'danger');
                });
        });
    });

    // Función para imprimir PDF
    function printPDF(pdfUrl) {
        // Detectar navegador para usar el método más adecuado
        const isFirefox = navigator.userAgent.toLowerCase().indexOf('firefox') > -1;
        const isChrome = navigator.userAgent.toLowerCase().indexOf('chrome') > -1;
        const isSafari = navigator.userAgent.toLowerCase().indexOf('safari') > -1 && !isChrome;
        const isEdge = navigator.userAgent.toLowerCase().indexOf('edge') > -1;

        // En Firefox y algunos navegadores, es mejor abrir en nueva ventana directamente
        if (isFirefox || isSafari) {
            printPDFNewWindow(pdfUrl);
            return;
        }

        // Método 1: Intentar usar iframe con manejo mejorado (para Chrome/Edge)
        const iframe = document.createElement('iframe');
        iframe.style.position = 'absolute';
        iframe.style.left = '-9999px';
        iframe.style.top = '-9999px';
        iframe.style.width = '1px';
        iframe.style.height = '1px';
        iframe.src = pdfUrl;

        let printed = false;
        let timeoutId;

        const cleanup = () => {
            if (timeoutId) clearTimeout(timeoutId);
            if (iframe && iframe.parentNode) {
                try {
                    iframe.parentNode.removeChild(iframe);
                } catch (e) {
                    // Ignorar errores de limpieza
                }
            }
        };

        const attemptPrint = () => {
            try {
                if (iframe.contentWindow && iframe.contentDocument) {
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                    printed = true;
                    
                    // Limpiar después de que el usuario termine de imprimir
                    setTimeout(cleanup, 10000);
                } else {
                    throw new Error('No se pudo acceder al contenido del iframe');
                }
            } catch (e) {
                console.warn('Error al imprimir con iframe, usando método alternativo:', e);
                cleanup();
                printPDFNewWindow(pdfUrl);
            }
        };

        document.body.appendChild(iframe);

        // Esperar a que el iframe cargue completamente
        iframe.onload = function() {
            // Dar tiempo extra para que el PDF se renderice
            setTimeout(attemptPrint, 2000);
        };

        // Timeout de seguridad en caso de que el iframe no cargue
        timeoutId = setTimeout(() => {
            if (!printed) {
                console.warn('Timeout al cargar PDF para imprimir, usando método alternativo');
                cleanup();
                printPDFNewWindow(pdfUrl);
            }
        }, 15000);

        // Manejar errores de carga
        iframe.onerror = function() {
            console.error('Error al cargar PDF para imprimir');
            cleanup();
            printPDFNewWindow(pdfUrl);
        };
    }

    // Función alternativa para imprimir PDF en nueva ventana
    function printPDFNewWindow(pdfUrl) {
        const printWindow = window.open(pdfUrl, '_blank', 'width=900,height=700,scrollbars=yes,resizable=yes');
        
        if (!printWindow) {
            alert('Por favor permite las ventanas emergentes para imprimir el documento.\n\nAlternativamente, puedes:\n1. Descargar el PDF\n2. Abrirlo en tu visor de PDF\n3. Imprimirlo desde ahí');
            return;
        }

        // Mostrar instrucciones al usuario
        showPrintInstructions();

        printWindow.onload = function() {
            // Dar tiempo para que el PDF se cargue completamente
            setTimeout(() => {
                try {
                    printWindow.focus();
                    printWindow.print();
                } catch (e) {
                    console.error('Error al imprimir:', e);
                    alert('No se pudo imprimir automáticamente. Usa Ctrl+P en la nueva ventana para imprimir.');
                }
            }, 2000);
        };

        // Timeout de seguridad
        setTimeout(() => {
            if (printWindow && !printWindow.closed) {
                try {
                    printWindow.focus();
                } catch (e) {
                    // Ignorar errores de enfoque
                }
            }
        }, 3000);
    }

    // Función para mostrar instrucciones de impresión
    function showPrintInstructions() {
        // Buscar el contenedor del PDF
        const pdfContainer = document.querySelector('.pdf-preview-container')?.closest('.card-body') || 
                            document.querySelector('.alert-warning')?.closest('.card-body');

        if (pdfContainer) {
            // Remover instrucciones anteriores
            const existingInstructions = pdfContainer.querySelector('.print-instructions');
            if (existingInstructions) existingInstructions.remove();

            const instructionsDiv = document.createElement('div');
            instructionsDiv.className = 'alert alert-info alert-dismissible fade show mt-2 print-instructions';
            instructionsDiv.innerHTML = `
                <i class="bi bi-info-circle me-2"></i>
                <strong>Ventana de impresión abierta:</strong> Si la impresión no inicia automáticamente, presiona <kbd>Ctrl+P</kbd> en la nueva ventana.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            pdfContainer.appendChild(instructionsDiv);

            // Remover instrucciones después de 10 segundos
            setTimeout(() => {
                if (instructionsDiv.parentNode) {
                    instructionsDiv.remove();
                }
            }, 10000);
        }
    }

    // Función para abrir PDF en nueva ventana
    function openPDFInNewWindow(pdfUrl) {
        window.open(pdfUrl, '_blank');
    }

    // Función para manejar el reemplazo del documento
    function handleDocumentReplace(input) {
        const file = input.files[0];

        if (!file) {
            return;
        }

        // Validar que sea un archivo PDF
        if (file.type !== 'application/pdf') {
            alert('Por favor selecciona un archivo PDF válido.');
            input.value = '';
            return;
        }

        // Validar tamaño del archivo (máximo 10MB)
        const maxSize = 10 * 1024 * 1024; // 10MB en bytes
        if (file.size > maxSize) {
            alert('El archivo es demasiado grande. El tamaño máximo permitido es 10MB.');
            input.value = '';
            return;
        }

        // Confirmar la subida/reemplazo
        const hasExistingDocument = {{ $contrato->documento && $contrato->documento !== 'No' ? 'true' : 'false' }};
        const confirmMessage = hasExistingDocument ?
            `¿Estás seguro de que deseas reemplazar el documento actual por "${file.name}"?\n\nEsto eliminará permanentemente el documento anterior.` :
            `¿Estás seguro de que deseas subir el documento "${file.name}"?`;

        const confirmAction = confirm(confirmMessage);

        if (!confirmAction) {
            input.value = '';
            return;
        }

        // Mostrar estado de carga
        const form = document.getElementById('reemplazarDocumentoForm');
        const loadingDiv = showDocumentLoading();

        // Enviar formulario
        const formData = new FormData(form);

        fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                hideDocumentLoading(loadingDiv);

                if (data.success) {
                    const successMessage = hasExistingDocument ?
                        'Documento reemplazado exitosamente' :
                        'Documento subido exitosamente';
                    showDocumentMessage(successMessage, 'success');
                    // Recargar la página después de 2 segundos para mostrar el nuevo documento
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    const errorMessage = hasExistingDocument ?
                        (data.message || 'Error al reemplazar el documento') :
                        (data.message || 'Error al subir el documento');
                    showDocumentMessage(errorMessage, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                hideDocumentLoading(loadingDiv);
                const errorMessage = hasExistingDocument ?
                    'Error al reemplazar el documento. Por favor, inténtelo de nuevo.' :
                    'Error al subir el documento. Por favor, inténtelo de nuevo.';
                showDocumentMessage(errorMessage, 'danger');
            });

        // Limpiar input
        input.value = '';
    }

    // Función para mostrar estado de carga del documento
    function showDocumentLoading() {
        // Buscar el contenedor donde mostrar el loading
        let container = document.querySelector('.pdf-preview-container');

        // Si no hay contenedor de PDF (no hay documento), buscar el card-body que tiene la alerta
        if (!container) {
            const warningAlert = document.querySelector('.alert-warning');
            if (warningAlert) {
                container = warningAlert.closest('.card-body');
            }
        }

        // Como último recurso, buscar cualquier card-body del área de documentos
        if (!container) {
            const documentHeaders = document.querySelectorAll('h5');
            for (let header of documentHeaders) {
                if (header.textContent.includes('Documento del Contrato')) {
                    const card = header.closest('.card');
                    if (card) {
                        container = card.querySelector('.card-body');
                        break;
                    }
                }
            }
        }

        if (!container) {
            console.error('No se encontró contenedor para mostrar el loading');
            return null;
        }

        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'position-absolute top-50 start-50 translate-middle bg-white p-3 rounded shadow-sm';
        loadingDiv.style.zIndex = '1000';
        loadingDiv.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Subiendo...</span>
                    </div>
                    <div class="mt-2 text-muted small">Subiendo documento...</div>
                </div>
            `;

        // Asegurar que el contenedor tenga posición relativa
        const currentPosition = window.getComputedStyle(container).position;
        if (currentPosition === 'static') {
            container.style.position = 'relative';
        }

        container.appendChild(loadingDiv);

        return loadingDiv;
    }

    // Función para ocultar estado de carga del documento
    function hideDocumentLoading(loadingDiv) {
        if (loadingDiv && loadingDiv.parentNode) {
            loadingDiv.parentNode.removeChild(loadingDiv);
        }
    }

    // Función para mostrar mensajes del documento
    function showDocumentMessage(message, type = 'success') {
        // Buscar el card-body donde mostrar el mensaje
        let cardBody = document.querySelector('.pdf-preview-container');
        if (cardBody) {
            cardBody = cardBody.closest('.card-body');
        }

        // Si no hay contenedor de PDF, buscar el card-body que tiene la alerta de warning
        if (!cardBody) {
            const warningAlert = document.querySelector('.alert-warning');
            if (warningAlert) {
                cardBody = warningAlert.closest('.card-body');
            }
        }

        // Si aún no encuentra, buscar por el título del documento
        if (!cardBody) {
            const documentHeaders = document.querySelectorAll('h5');
            for (let header of documentHeaders) {
                if (header.textContent.includes('Documento del Contrato')) {
                    const card = header.closest('.card');
                    if (card) {
                        cardBody = card.querySelector('.card-body');
                        break;
                    }
                }
            }
        }

        if (!cardBody) {
            console.error('No se encontró contenedor para mostrar el mensaje');
            return;
        }

        // Remover mensajes anteriores
        const existingAlerts = cardBody.querySelectorAll('.document-alert');
        existingAlerts.forEach(alert => alert.remove());

        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show mt-2 document-alert`;
        const icon = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle';
        alertDiv.innerHTML = `
                <i class="bi ${icon} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

        cardBody.appendChild(alertDiv);

        // Remover alerta después de unos segundos
        const timeout = type === 'success' ? 5000 : 8000;
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, timeout);
    }
</script>

<script>
    // Función para manejar la confirmación de acciones con feedback visual
    function handleContractAction(action, formId, modalId) {
        // Obtener elementos
        const modal = document.getElementById(modalId);
        const form = document.getElementById(formId);
        const modalInstance = bootstrap.Modal.getInstance(modal);
        
        // Mostrar estado de carga en el modal
        const modalBody = modal.querySelector('.modal-body');
        const originalContent = modalBody.innerHTML;
        
        modalBody.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Procesando...</span>
                </div>
                <h6>Procesando ${action.toLowerCase()}...</h6>
                <p class="text-muted">Por favor espera un momento.</p>
            </div>
        `;
        
        // Deshabilitar botones del modal
        const modalButtons = modal.querySelectorAll('.modal-footer .btn');
        modalButtons.forEach(btn => btn.disabled = true);
        
        // Enviar formulario
        form.submit();
    }

    // Event listeners para los modales al abrirse
    document.addEventListener('DOMContentLoaded', function() {
        // Modal de suspender
        const suspenderModal = document.getElementById('suspenderModal');
        if (suspenderModal) {
            suspenderModal.addEventListener('show.bs.modal', function() {
                // Enfocar el botón de cancelar por defecto
                setTimeout(() => {
                    const cancelBtn = suspenderModal.querySelector('.btn-secondary');
                    if (cancelBtn) cancelBtn.focus();
                }, 300);
            });
        }

        // Modal de cancelar
        const cancelarModal = document.getElementById('cancelarModal');
        if (cancelarModal) {
            cancelarModal.addEventListener('show.bs.modal', function() {
                setTimeout(() => {
                    const cancelBtn = cancelarModal.querySelector('.btn-secondary');
                    if (cancelBtn) cancelBtn.focus();
                }, 300);
            });
        }

        // Modal de finalizar
        const finalizarModal = document.getElementById('finalizarModal');
        if (finalizarModal) {
            finalizarModal.addEventListener('show.bs.modal', function() {
                setTimeout(() => {
                    const cancelBtn = finalizarModal.querySelector('.btn-secondary');
                    if (cancelBtn) cancelBtn.focus();
                }, 300);
            });
        }

        // Modal de reactivar
        const reactivarModal = document.getElementById('reactivarModal');
        if (reactivarModal) {
            reactivarModal.addEventListener('show.bs.modal', function() {
                setTimeout(() => {
                    const cancelBtn = reactivarModal.querySelector('.btn-secondary');
                    if (cancelBtn) cancelBtn.focus();
                }, 300);
            });
        }

        // Agregar confirmación adicional para acciones críticas
        const cancelarBtn = document.querySelector('#cancelarModal .btn-danger');
        if (cancelarBtn) {
            cancelarBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Doble confirmación para cancelar
                const finalConfirm = confirm('CONFIRMACIÓN FINAL:\n\n¿Realmente deseas cancelar este contrato?\n\nEsta acción afectará al cliente y sus pagos.');
                
                if (finalConfirm) {
                    handleContractAction('cancelar', 'cancelar-contrato-form', 'cancelarModal');
                }
            });
        }

        // Event listeners para otros botones de acción
        const suspenderBtn = document.querySelector('#suspenderModal .btn-warning');
        if (suspenderBtn) {
            suspenderBtn.addEventListener('click', function(e) {
                e.preventDefault();
                handleContractAction('suspender', 'suspender-contrato-form', 'suspenderModal');
            });
        }

        const finalizarBtn = document.querySelector('#finalizarModal .btn-success');
        if (finalizarBtn) {
            finalizarBtn.addEventListener('click', function(e) {
                e.preventDefault();
                handleContractAction('finalizar', 'finalizar-contrato-form', 'finalizarModal');
            });
        }

        const reactivarBtn = document.querySelector('#reactivarModal .btn-info');
        if (reactivarBtn) {
            reactivarBtn.addEventListener('click', function(e) {
                e.preventDefault();
                handleContractAction('reactivar', 'reactivar-contrato-form', 'reactivarModal');
            });
        }

        // Atajos de teclado para los modales
        document.addEventListener('keydown', function(e) {
            // Detectar si hay un modal abierto
            const openModal = document.querySelector('.modal.show');
            if (!openModal) return;

            // Escape para cerrar
            if (e.key === 'Escape') {
                const modalInstance = bootstrap.Modal.getInstance(openModal);
                if (modalInstance) modalInstance.hide();
            }

            // Enter para confirmar (solo si el botón de acción está enfocado)
            if (e.key === 'Enter') {
                const focusedElement = document.activeElement;
                if (focusedElement && focusedElement.classList.contains('btn') && 
                    !focusedElement.classList.contains('btn-secondary')) {
                    focusedElement.click();
                }
            }
        });
    });
</script>

<!-- Modal para seleccionar período del estado de cuenta -->
<div class="modal fade" id="estadoCuentaModal" tabindex="-1" aria-labelledby="estadoCuentaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="estadoCuentaModalLabel">
                    <i class="bi bi-file-text me-2"></i>Seleccionar Período del Estado de Cuenta
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @php
                // Calcular los períodos basados en la frecuencia y número de cuotas
                $fechaInicio = $contrato->fecha_inicio ? \Carbon\Carbon::parse($contrato->fecha_inicio) : now();
                $frecuenciaDias = $contrato->frecuencia_cuotas ?? 30;
                $numeroCuotas = $contrato->numero_cuotas ?? 1;
                $periodos = [];
                $periodosCompletos = [];
                
                // Validar que tengamos datos válidos
                if ($numeroCuotas > 0 && $frecuenciaDias > 0 && $contrato->fecha_inicio) {
                    for ($i = 0; $i < $numeroCuotas; $i++) {
                        $fechaInicioPeríodo = $fechaInicio->copy()->addDays($frecuenciaDias * $i);
                        $fechaFinPeríodo = $fechaInicio->copy()->addDays($frecuenciaDias * ($i + 1))->subDay();
                        
                        $periodo = [
                            'numero' => $i + 1,
                            'fecha_inicio' => $fechaInicioPeríodo,
                            'fecha_fin' => $fechaFinPeríodo,
                            'es_actual' => now()->between($fechaInicioPeríodo, $fechaFinPeríodo),
                            'ya_paso' => now()->greaterThan($fechaFinPeríodo)
                        ];
                        
                        // Agregar a la lista completa
                        $periodos[] = $periodo;
                        
                        // Solo agregar períodos que ya pasaron a la lista de períodos completados
                        if ($periodo['ya_paso']) {
                            $periodosCompletos[] = $periodo;
                        }
                    }
                }
                @endphp

                <div class="mb-3">
                    <div class="alert alert-info" role="alert">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Información del contrato:</strong><br>
                        Este contrato tiene <strong>{{ $numeroCuotas }} períodos</strong> de 
                        <strong>{{ $frecuenciaDias }} días</strong> cada uno, iniciando el 
                        <strong>{{ \Carbon\Carbon::parse($contrato->fecha_inicio)->locale('es')->translatedFormat('d \d\e F \d\e Y') }}</strong>.
                        <br><small class="text-muted mt-1 d-block">
                            <i class="bi bi-calendar-check me-1"></i>
                            Se muestran únicamente los períodos con fechas que ya han transcurrido 
                            (<strong>{{ count($periodosCompletos) }}</strong> de {{ $numeroCuotas }} períodos).
                        </small>
                    </div>
                    <p class="text-muted mb-0">
                        <i class="bi bi-hand-index me-1"></i>
                        <strong>Selecciona un período completado</strong> para generar un estado de cuenta específico, 
                        o usa el botón <strong>"Ver Estado Completo"</strong> para ver todo el historial del contrato.
                    </p>
                </div>

                <div class="row g-3">
                    @foreach($periodosCompletos as $periodo)
                    @php
                    $fechaInicioFormateada = $periodo['fecha_inicio']->locale('es')->translatedFormat('d \d\e F \d\e Y');
                    $fechaFinFormateada = $periodo['fecha_fin']->locale('es')->translatedFormat('d \d\e F \d\e Y');
                    @endphp
                    
                    <div class="col-md-6">
                        <div class="card h-100 periodo-card border-success" 
                             style="cursor: pointer;" 
                             onclick="seleccionarPeriodo({{ $periodo['numero'] }}, '{{ $periodo['fecha_inicio']->format('Y-m-d') }}', '{{ $periodo['fecha_fin']->format('Y-m-d') }}')">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="card-title mb-0">
                                        <i class="bi bi-calendar-range me-1"></i>
                                        Período {{ $periodo['numero'] }}
                                    </h6>
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>Completado
                                    </span>
                                </div>
                                
                                <div class="mb-2">
                                    <small class="text-muted d-block">
                                        <i class="bi bi-calendar-check me-1"></i>
                                        <strong>Inicio:</strong> {{ $fechaInicioFormateada }}
                                    </small>
                                    <small class="text-muted d-block">
                                        <i class="bi bi-calendar-x me-1"></i>
                                        <strong>Fin:</strong> {{ $fechaFinFormateada }}
                                    </small>
                                </div>
                                
                                <div class="mb-2">
                                    <small class="text-success d-block">
                                        <i class="bi bi-hourglass-bottom me-1"></i>
                                        <strong>Días transcurridos:</strong> {{ now()->diffInDays($periodo['fecha_fin']) }} días atrás
                                    </small>
                                </div>
                                
                                <div class="text-center">
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>
                                        {{ $frecuenciaDias }} días de duración
                                    </small>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent text-center py-2">
                                <small class="text-success">
                                    <i class="bi bi-hand-index me-1"></i>
                                    Clic para generar estado de cuenta
                                </small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                @if(empty($periodos))
                <div class="text-center py-4">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>No se pudieron calcular los períodos.</strong><br>
                        <small class="text-muted">
                            Verifica que el contrato tenga:
                            <ul class="list-unstyled mt-2 mb-0">
                                <li>• Fecha de inicio válida</li>
                                <li>• Número de cuotas mayor a 0</li>
                                <li>• Frecuencia de cuotas configurada</li>
                            </ul>
                        </small>
                    </div>
                    <p class="text-muted">
                        Puedes ver el estado completo del contrato usando el botón de abajo.
                    </p>
                </div>
                @elseif(empty($periodosCompletos))
                <div class="text-center py-4">
                    <div class="alert alert-info">
                        <i class="bi bi-calendar-x me-2"></i>
                        <strong>No hay períodos completados disponibles.</strong><br>
                        <small class="text-muted">
                            Este contrato aún no tiene períodos con fechas que hayan transcurrido.
                            Los períodos estarán disponibles conforme vayan completándose sus fechas.
                        </small>
                    </div>
                    <div class="mt-3">
                        @php
                        $proximoPeriodo = collect($periodos)->first(function($periodo) {
                            return !$periodo['ya_paso'];
                        });
                        @endphp
                        
                        @if($proximoPeriodo)
                        <div class="card border-secondary">
                            <div class="card-body text-center py-3">
                                <h6 class="card-title mb-2">
                                    <i class="bi bi-clock me-1"></i>Próximo período disponible
                                </h6>
                                <p class="mb-1">
                                    <strong>Período {{ $proximoPeriodo['numero'] }}</strong>
                                </p>
                                <small class="text-muted">
                                    Estará disponible después del 
                                    <strong>{{ $proximoPeriodo['fecha_fin']->locale('es')->translatedFormat('d \d\e F \d\e Y') }}</strong>
                                </small>
                            </div>
                        </div>
                        @endif
                    </div>
                    <p class="text-muted mt-3">
                        Mientras tanto, puedes ver el estado completo del contrato usando el botón de abajo.
                    </p>
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Cancelar
                </button>
                <a href="{{ route('contratos.estado', $contrato->id) }}" class="btn btn-primary">
                    <i class="bi bi-file-text me-1"></i>Ver Estado Completo
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.periodo-card {
    transition: all 0.3s ease;
}

.periodo-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.periodo-card.border-primary:hover {
    box-shadow: 0 8px 25px rgba(13, 110, 253, 0.3);
}

.periodo-card.border-success:hover {
    box-shadow: 0 8px 25px rgba(25, 135, 84, 0.3);
}

.periodo-card.border-secondary:hover {
    box-shadow: 0 8px 25px rgba(108, 117, 125, 0.3);
}
</style>

<script>
function seleccionarPeriodo(numero, fechaInicio, fechaFin) {
    // Construir URL con parámetros del período seleccionado
    const url = "{{ route('contratos.estado', $contrato->id) }}" + 
                "?periodo=" + numero + 
                "&fecha_inicio=" + fechaInicio + 
                "&fecha_fin=" + fechaFin;
    
    // Mostrar loading en el modal
    const modal = document.getElementById('estadoCuentaModal');
    const originalContent = modal.querySelector('.modal-body').innerHTML;
    
    modal.querySelector('.modal-body').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Generando estado de cuenta...</span>
            </div>
            <p class="mt-3 mb-0">Generando estado de cuenta para el período ${numero}...</p>
        </div>
    `;
    
    // Redirigir después de un breve delay para mostrar el loading
    setTimeout(() => {
        window.location.href = url;
    }, 500);
}
</script>

@endsection