@extends('layouts.app')

@section('content')
@php
    // Zona horaria de México
    date_default_timezone_set('America/Mexico_City');
    $hour = date('H');
    if ($hour >= 6 && $hour < 12) {
        $greeting = 'Buenos días';
    } elseif ($hour >= 12 && $hour < 19) {
        $greeting = 'Buenas tardes';
    } else {
        $greeting = 'Buenas noches';
    }
@endphp

<style>
.agenda-card {
    transition: all 0.3s ease;
    min-height: 200px;
}

.agenda-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
}

.agenda-card.today {
    background: linear-gradient(135deg, #e3f2fd 0%, #f8f9fa 100%);
    border-left: 4px solid #2196f3 !important;
}

.pago-item {
    transition: all 0.2s ease;
    cursor: pointer;
}

.pago-item:hover {
    background-color: #f8f9fa !important;
    transform: translateX(2px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.pago-link {
    transition: all 0.2s ease;
}

.pago-link:hover .pago-item {
    transform: translateX(4px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.agenda-nav-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
    border: 2px solid #E1B240;
    color: white;
    transition: all 0.3s ease;
}

.agenda-nav-btn:hover {
    background: linear-gradient(135deg, #79481D 0%, #E1B240 100%);
    border-color: #79481D;
    color: white;
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(225, 178, 64, 0.4);
}

.agenda-nav-btn.btn-primary {
    background: linear-gradient(135deg, #79481D 0%, #E1B240 100%);
    border-color: #79481D;
}

.agenda-nav-btn.btn-primary:hover {
    background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
    border-color: #E1B240;
}

.agenda-header {
    background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

@media (max-width: 768px) {
    .agenda-card {
        min-height: 150px;
    }
    
    .col {
        min-width: 120px;
    }
}
</style>

{{-- Rainbow background container --}}
<div class="rainbow-background">
    @for ($i = 1; $i <= 25; $i++)
        <div class="rainbow"></div>
    @endfor
    
    <div class="h"></div>
    <div class="v"></div>
</div>

<div class="container" style="position: relative; z-index: 10;">
    <div class="mb-4 text-start">
        <h3 class="display-2 fw-bold" style="line-height: 0.8; opacity: 0.6; letter-spacing: -2px;">¡{{ $greeting }}, 
            <br>
        {{ Auth::user()->name }}!</h3>
    </div>
    
    {{-- Agenda Minimalista - Solo para administradores --}}
    @if(Auth::user()->role === 'admin')
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex justify-content-start mt-3 mb-3">
                <div class="text-start">
                    <h4 class="fw-bold agenda-header mb-0 me-3">
                        <i class="bi bi-calendar-week me-2"></i>Agenda de Pagos
                    </h4>
                    <small class="text-muted">
                        Semana del {{ $agendaDias->first()['fecha']->format('d/m') }} 
                        al {{ $agendaDias->last()['fecha']->format('d/m/Y') }}
                    </small>
                    @if($currentWeekOffset == 0)
                    <div class="mt-1">
                        <span class="badge" style="background: linear-gradient(135deg, #79481D 0%, #E1B240 100%); color: white;">
                            <i class="bi bi-clock me-1"></i>Semana actual
                        </span>
                    </div>
                    @endif
                </div>
            </div>
            <div class="btn-group" role="group">
                <a href="{{ route('home', ['week' => $currentWeekOffset - 1]) }}" 
                   class="btn btn-outline-primary agenda-nav-btn" 
                   title="Semana anterior"
                   style="background: none; color: #79481D;">
                    <i class="bi bi-chevron-left"></i>
                </a>
                @if($currentWeekOffset != 0)
                    <a href="{{ route('home') }}" 
                       class="btn btn-outline-primary agenda-nav-btn"
                       title="Semana actual"
                       style="background: none; color: #79481D;">
                        <i class="bi bi-house-fill"></i>
                    </a>
                @endif
                <a href="{{ route('home', ['week' => $currentWeekOffset + 1]) }}" 
                   class="btn btn-outline-primary agenda-nav-btn"
                   title="Semana siguiente"
                   style="background: none; color: #79481D;">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>
        
        
        <div class="row g-2">
            @foreach($agendaDias as $dia)
                <div class="col">
                    <div class="card agenda-card border-0 shadow-sm {{ $dia['fecha']->isToday() ? 'today' : '' }}">
                        <div class="card-body p-3">
                            {{-- Cabecera del día --}}
                            <div class="text-center mb-3">
                                <div class="fw-bold text-uppercase small text-muted">
                                    @if($dia['fecha']->isToday())
                                        <i class="bi bi-circle-fill text-primary ms-1" style="font-size: .75rem; "></i>
                                    @endif
                                    {{ $dia['dia_nombre'] }}
                                </div>
                                <div class="h4 display-6 fw-bold text-secondary mb-0">
                                    {{ $dia['dia_numero'] }}
                                </div>
                                <div class="small text-muted">
                                    {{ $dia['mes'] }}
                                </div>
                            </div>
                            
                            {{-- Lista de pagos pendientes y hechos --}}
                            @if($dia['pagos_pendientes']->count() > 0 || $dia['pagos_hechos']->count() > 0)
                                <div class="border-top pt-2">
                                    {{-- Resumen de pagos --}}
                                    <div class="small mb-2 text-center">
                                        @if($dia['pagos_pendientes']->count() > 0)
                                            <span class="badge badge-sm me-1" style="background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); color: white; font-size: 0.7rem;">
                                                <i class="bi bi-clock me-1"></i>{{ $dia['pagos_pendientes']->count() }} pendientes
                                            </span>
                                        @endif
                                        @if($dia['pagos_hechos']->count() > 0)
                                            <span class="badge bg-success badge-sm" style="font-size: 0.7rem;">
                                                <i class="bi bi-check-circle me-1"></i>{{ $dia['pagos_hechos']->count() }} hechos
                                            </span>
                                        @endif
                                    </div>
                                    
                                    {{-- Pagos pendientes --}}
                                    @foreach($dia['pagos_pendientes'] as $pago)
                                        <a href="{{ route('contratos.show', $pago->contrato->id) }}" class="text-decoration-none pago-link">
                                            <div class="pago-item small mb-2 p-2 bg-white rounded border-start border-3" 
                                                 style="border-left-color: #E1B240 !important;"
                                                 title="PENDIENTE - Cliente: {{ $pago->contrato->cliente->nombre ?? 'Sin cliente' }} - Contrato #{{ $pago->contrato->id ?? 'N/A' }} - Click para ver detalles">
                                                <div class="fw-bold text-truncate">
                                                    <i class="bi bi-person-fill me-1" style="color: #E1B240;"></i>
                                                    {{ Str::limit($pago->contrato->cliente->nombre ?? 'Sin cliente', 11) }}
                                                </div>
                                                <div class="fw-bold" style="color: #E1B240;">
                                                    <i class="bi bi-currency-dollar"></i>{{ number_format($pago->monto, 0) }}
                                                </div>
                                                @if($pago->numero_cuota)
                                                    <div class="text-muted" style="font-size: 0.65rem;">
                                                        <i class="bi bi-list-ol me-1"></i>Cuota #{{ $pago->numero_cuota }}
                                                    </div>
                                                @endif
                                            </div>
                                        </a>
                                    @endforeach
                                    
                                    {{-- Pagos hechos --}}
                                    @foreach($dia['pagos_hechos'] as $pago)
                                        <a href="{{ route('contratos.show', $pago->contrato->id) }}" class="text-decoration-none pago-link">
                                            <div class="pago-item small mb-2 p-2 bg-white rounded border-start border-3 border-success"
                                                 title="COMPLETADO - Cliente: {{ $pago->contrato->cliente->nombre ?? 'Sin cliente' }} - Contrato #{{ $pago->contrato->id ?? 'N/A' }} - Click para ver detalles">
                                                <div class="fw-bold text-truncate">
                                                    <i class="bi bi-person-check-fill me-1 text-success"></i>
                                                    {{ Str::limit($pago->contrato->cliente->nombre ?? 'Sin cliente', 11) }}
                                                </div>
                                                <div class="text-success fw-bold">
                                                    <i class="bi bi-check-circle me-1"></i>${{ number_format($pago->monto, 0) }}
                                                </div>
                                                @if($pago->numero_cuota)
                                                    <div class="text-muted" style="font-size: 0.65rem;">
                                                        <i class="bi bi-list-ol me-1"></i>Cuota #{{ $pago->numero_cuota }}
                                                    </div>
                                                @endif
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center text-muted small mt-4">
                                    <i class="bi bi-calendar-x fs-4" style="color: #E1B240;"></i>
                                    <div class="mt-2 fw-bold">Sin pagos programados</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        
    </div>
    @endif
    
    {{-- Sección para Empleados - Solo para usuarios con rol empleado --}}
    @if(Auth::user()->role !== 'admin' && ($empleadoContratos->count() > 0 || $empleadoAgenda->count() > 0 || $empleadoPagosVencidos->count() > 0))
    <div class="mb-5">
        <div class="d-flex justify-content-start mt-3 mb-4">
            <div class="text-start">
                <h4 class="fw-bold agenda-header mb-0 me-3">
                    <i class="bi bi-briefcase me-2"></i>Mis Datos de Trabajo
                </h4>
                <small class="text-muted">
                    Contratos asignados y agenda personal
                </small>
            </div>
        </div>


        
        <div class="row g-4">
            {{-- Columna 1: Contratos Asignados --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pb-2">
                        <h5 class="fw-bold mb-0" style="color: #79481D;">
                            <i class="bi bi-file-text me-2"></i>Mis Contratos Asignados
                        </h5>
                        <small class="text-muted">{{ $empleadoContratos->count() }} contratos activos</small>
                    </div>
                    <div class="card-body">
                        @if($empleadoContratos->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($empleadoContratos as $contrato)
                                    <a href="{{ route('contratos.show', $contrato->id) }}" class="list-group-item list-group-item-action border-0 px-0 py-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="fw-bold text-truncate" style="color: #79481D;">
                                                    <i class="bi bi-box me-1"></i>
                                                    {{ $contrato->paquete->nombre ?? 'Sin paquete' }}#{{ $contrato->paquete->id ?? 'Sin ID' }}
                                                </div>
                                                <div class="small text-muted mb-1">
                                                    <i class="bi bi-person-fill me-1"></i>
                                                    {{ $contrato->cliente->nombre ?? 'Sin cliente' }} {{ $contrato->cliente->apellido ?? '' }}
                                                </div>
                                                <div class="small text-muted">
                                                    <i class="bi bi-calendar me-1"></i>
                                                    @php
                                                        $proximoPago = $contrato->pagos()
                                                            ->where('estado', 'pendiente')
                                                            ->orderBy('fecha_pago', 'asc')
                                                            ->first();
                                                    @endphp
                                                    Fecha de pago: 
                                                    @if($proximoPago)
                                                        {{ \Carbon\Carbon::parse($proximoPago->fecha_pago)->translatedFormat('d \d\e F \d\e Y') }}
                                                    @else
                                                        Sin cuotas proximas
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-success small">
                                                    @if($proximoPago)
                                                        ${{ number_format($proximoPago->monto, 0) }}
                                                    @else
                                                        NA
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-briefcase fs-1" style="color: #E1B240;"></i>
                                <div class="mt-3">
                                    <h6 class="fw-bold">No tienes contratos asignados</h6>
                                    <p class="mb-0">Los contratos aparecerán aquí cuando te sean asignados.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            {{-- Columna 2: Agenda Personal de 7 días --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pb-2">
                        <h5 class="fw-bold mb-0" style="color: #79481D;">
                            <i class="bi bi-calendar-week me-2"></i>Mi Agenda
                        </h5>
                        <small class="text-muted">Pagos programados para los próximos 7 días</small>
                    </div>
                    <div class="card-body p-2">
                        @if($empleadoAgenda->count() > 0)
                            @foreach($empleadoAgenda as $dia)
                                <div class=" rounded p-3 mb-2 {{ $dia['fecha']->isToday() ? 'bg-light border-primary' : '' }}">
                                    {{-- Cabecera del día --}}
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <div class="fw-bold small text-uppercase text-muted">
                                                @if($dia['fecha']->isToday())
                                                    <i class="bi bi-circle-fill text-primary me-1" style="font-size: .5rem;"></i>
                                                @endif
                                                {{ $dia['dia_nombre'] }}
                                            </div>
                                            <div class="h6 mb-0 fw-bold" style="color: #79481D;">
                                                {{ $dia['dia_numero'] }} {{ $dia['mes'] }}
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            @if($dia['pagos_pendientes']->count() > 0)
                                                <span class="badge badge-sm me-1" style="background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); color: white; font-size: 0.6rem;">
                                                    {{ $dia['pagos_pendientes']->count() }} pendientes
                                                </span>
                                            @endif
                                            @if($dia['pagos_hechos']->count() > 0)
                                                <span class="badge bg-success badge-sm" style="font-size: 0.6rem;">
                                                    {{ $dia['pagos_hechos']->count() }} hechos
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    {{-- Lista de pagos --}}
                                    @if($dia['pagos_pendientes']->count() > 0 || $dia['pagos_hechos']->count() > 0)
                                        <div class="small">
                                            <div class="row g-2">
                                                {{-- Pagos pendientes --}}
                                                @foreach($dia['pagos_pendientes'] as $pago)
                                                    <div class="col-6 col-md-3">
                                                        <a href="{{ route('contratos.show', $pago->contrato->id) }}" class="text-decoration-none">
                                                            <div class="py-2 px-2 bg-white rounded border-start border-3 mb-2 h-100"
                                                                 style="border-left-color: #E1B240 !important;">
                                                                <div class="fw-bold text-truncate" style="color: #79481D;">
                                                                    <i class="bi bi-person-fill me-1" style="color: #E1B240;"></i>
                                                                    {{ Str::limit($pago->contrato->cliente->nombre ?? 'Sin cliente', 15) }}
                                                                </div>
                                                                <div class="fw-bold" style="color: #E1B240;">
                                                                    ${{ number_format($pago->monto, 0) }}
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </div>
                                                @endforeach

                                                {{-- Pagos hechos --}}
                                                @foreach($dia['pagos_hechos'] as $pago)
                                                    <div class="col-6 col-md-3">
                                                        <a href="{{ route('contratos.show', $pago->contrato->id) }}" class="text-decoration-none">
                                                            <div class="py-2 px-2 bg-white rounded border-start border-3 border-success mb-2 h-100">
                                                                <div class="fw-bold text-truncate text-success">
                                                                    <i class="bi bi-person-check-fill me-1"></i>
                                                                    {{ Str::limit($pago->contrato->cliente->nombre ?? 'Sin cliente', 15) }}
                                                                </div>
                                                                <div class="text-success fw-bold">
                                                                    <i class="bi bi-check-circle me-1"></i>${{ number_format($pago->monto, 0) }}
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-center text-muted small">
                                            <i class="bi bi-calendar-x" style="color: #E1B240;"></i>
                                            Sin pagos programados
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-calendar-x fs-1" style="color: #E1B240;"></i>
                                <div class="mt-3">
                                    <h6 class="fw-bold">No hay pagos programados</h6>
                                    <p class="mb-0">Los pagos de tus contratos aparecerán aquí.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    {{-- Sección de Pagos Vencidos - Solo para empleados --}}
    @if(Auth::user()->role !== 'admin' && $empleadoPagosVencidos->count() > 0)
    <div class="mb-5">
        <div class="d-flex justify-content-start mt-3 mb-4">
            <div class="text-start">
                <h4 class="fw-bold mb-0 me-3" style="color: #dc3545;">
                    <i class="bi bi-exclamation-triangle me-2"></i>Pagos Vencidos
                </h4>
                <small class="text-muted">
                    {{ $empleadoPagosVencidos->count() }} pagos que exceden el período de tolerancia
                </small>
                <div class="mt-1">
                    <span class="badge bg-danger">
                        <i class="bi bi-clock-history me-1"></i>Requieren atención inmediata
                    </span>
                    @if(toleranciaPagos() > 0)
                        <span class="badge bg-warning text-dark ms-1">
                            <i class="bi bi-info-circle me-1"></i>Tolerancia: {{ toleranciaPagos() }} días
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-start border-4 border-danger" style="border-top: 0; border-right: 0; border-bottom: 0;">
            <div class="card-header bg-light border-0 pb-2">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-danger">
                        <i class="bi bi-calendar-x me-2"></i>Pagos Atrasados
                    </h5>
                    <span class="badge bg-danger">{{ $empleadoPagosVencidos->count() }} pendientes</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($empleadoPagosVencidos as $pago)
                        <div class="col-lg-4 col-md-6">
                            <a href="{{ route('contratos.show', $pago->contrato->id) }}" class="text-decoration-none">
                                <div class="card border-0 bg-light h-100 pago-item" style="border-left: 4px solid #dc3545 !important;">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="flex-grow-1">
                                                <div class="fw-bold text-truncate text-danger mb-1">
                                                    <i class="bi bi-person-fill me-1"></i>
                                                    {{ $pago->contrato->cliente->nombre ?? 'Sin cliente' }} {{ $pago->contrato->cliente->apellido ?? '' }}
                                                </div>
                                                <div class="small text-muted mb-1">
                                                    <i class="bi bi-box me-1"></i>
                                                    Contrato #{{ $pago->contrato->id }} - {{ $pago->contrato->paquete->nombre ?? 'Sin paquete' }}
                                                </div>
                                                @if($pago->numero_cuota)
                                                    <div class="small text-muted mb-2">
                                                        <i class="bi bi-list-ol me-1"></i>Cuota #{{ $pago->numero_cuota }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="fw-bold h5 mb-0 text-danger">
                                                <i class="bi bi-currency-dollar"></i>{{ number_format($pago->monto, 0) }}
                                            </div>
                                            <div class="text-end">
                                                <div class="small text-danger fw-bold">
                                                    <i class="bi bi-calendar-x me-1"></i>
                                                    Venció: {{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}
                                                </div>
                                                <div class="small text-muted">
                                                    @php
                                                        $diasRetraso = diasDeRetraso($pago->fecha_pago, $pago->estado);
                                                    @endphp
                                                    @if($diasRetraso > 0)
                                                        <span class="text-danger fw-bold">
                                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                                            {{ $diasRetraso }} día{{ $diasRetraso != 1 ? 's' : '' }} de retraso
                                                        </span>
                                                    @else
                                                        <span class="text-warning">
                                                            <i class="bi bi-clock me-1"></i>
                                                            En período de tolerancia
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
                
                @if($empleadoPagosVencidos->count() > 6)
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Mostrando los pagos vencidos más recientes. 
                            <a href="#" class="text-primary">Ver todos los pagos vencidos</a>
                        </small>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif
    
    {{-- Script para tooltips --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar tooltips de Bootstrap si están disponibles
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
        });
    </script>
    
</div>
@endsection
